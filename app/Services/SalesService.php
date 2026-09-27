<?php

namespace App\Services;

use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class SalesService
{
    public function __construct(
        private readonly InventoryService $inventoryService
    ) {}

    /**
     * Generate Nomor Faktur / Invoice Penjualan unik. (e.g. INV-202609-0001)
     */
    public function generateInvoiceNumber(): string
    {
        $prefix = 'INV-' . date('Ym') . '-';
        $latest = Sale::withTrashed()
            ->where('invoice_number', 'like', "{$prefix}%")
            ->orderByDesc('id')
            ->value('invoice_number');

        if (!$latest) {
            return $prefix . '0001';
        }

        $number = (int) substr($latest, strlen($prefix)) + 1;
        return $prefix . str_pad((string) $number, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Membuat Transaksi Penjualan baru.
     * Jika status = COMPLETED, sistem langsung memotong stok secara atomik.
     */
    public function createSale(array $data, array $items = [], ?int $userId = null, ?bool $completeImmediately = null): Sale
    {
        if (empty($items) && !empty($data['items'])) {
            $items = $data['items'];
        }

        $userId = $userId ?? ($data['created_by'] ?? (auth()->id() ?? 1));

        if (empty($items)) {
            throw new InvalidArgumentException("Transaksi penjualan harus memiliki minimal satu item produk.");
        }

        return DB::transaction(function () use ($data, $items, $userId, $completeImmediately) {
            $invoiceNumber = $data['invoice_number'] ?? $this->generateInvoiceNumber();
            $saleDate = !empty($data['sale_date']) ? Carbon::parse($data['sale_date']) : Carbon::now();
            $warehouseId = (int) $data['warehouse_id'];
            $discount = (float) ($data['discount'] ?? 0);
            $tax = (float) ($data['tax'] ?? 0);
            $shippingCost = (float) ($data['shipping_cost'] ?? 0);
            $paidAmount = (float) ($data['paid_amount'] ?? 0);
            
            $status = $completeImmediately !== null
                ? ($completeImmediately ? Sale::STATUS_COMPLETED : Sale::STATUS_DRAFT)
                : ($data['status'] ?? Sale::STATUS_COMPLETED);

            $subtotal = 0;
            $calculatedItems = [];

            // 1. Calculate and validate items
            foreach ($items as $item) {
                $product = Product::findOrFail($item['product_id']);
                $qty = (int) $item['quantity'];
                if ($qty <= 0) {
                    throw new InvalidArgumentException("Kuantitas produk [{$product->name}] harus lebih besar dari 0.");
                }

                $unitPrice = isset($item['unit_price']) ? (float) $item['unit_price'] : (float) $product->selling_price;
                $discountPercent = (float) ($item['discount_percent'] ?? 0);
                $taxPercent = (float) ($item['tax_percent'] ?? 0);

                $lineBase = $qty * $unitPrice;
                $lineDisc = $lineBase * ($discountPercent / 100);
                $lineAfterDisc = $lineBase - $lineDisc;
                $lineTax = $lineAfterDisc * ($taxPercent / 100);
                $itemSubtotal = max(0, $lineAfterDisc + $lineTax);

                $subtotal += $lineBase;

                $calculatedItems[] = [
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'discount_percent' => $discountPercent,
                    'tax_percent' => $taxPercent,
                    'subtotal' => $itemSubtotal,
                ];
            }

            $total = max(0, $subtotal - $discount + $tax + $shippingCost);
            $changeAmount = max(0, $paidAmount - $total);

            /** @var Sale $sale */
            $sale = Sale::create([
                'invoice_number' => $invoiceNumber,
                'warehouse_id' => $warehouseId,
                'customer_name' => $data['customer_name'] ?? 'Pelanggan Umum',
                'customer_phone' => $data['customer_phone'] ?? null,
                'sale_date' => $saleDate,
                'status' => $status,
                'payment_method' => $data['payment_method'] ?? 'CASH',
                'payment_status' => $data['payment_status'] ?? 'PAID',
                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax' => $tax,
                'shipping_cost' => $shippingCost,
                'paid_amount' => $paidAmount,
                'change_amount' => $changeAmount,
                'total' => $total,
                'notes' => $data['notes'] ?? null,
                'created_by' => $userId,
            ]);

            foreach ($calculatedItems as $cItem) {
                $sale->items()->create($cItem);
            }

            // 2. If completed, execute atomic stock reduction
            if ($status === Sale::STATUS_COMPLETED) {
                foreach ($calculatedItems as $cItem) {
                    $note = "Penjualan Faktur #{$sale->invoice_number}" . ($sale->customer_name ? " ({$sale->customer_name})" : "");
                    $this->inventoryService->stockOut(
                        productId: $cItem['product_id'],
                        warehouseId: $warehouseId,
                        quantity: $cItem['quantity'],
                        type: InventoryTransaction::TYPE_SALE,
                        notes: $note,
                        userId: $userId,
                        refType: 'Sale',
                        referenceId: $sale->invoice_number,
                        date: $saleDate
                    );
                }
            }

            return $sale->load(['items.product', 'warehouse', 'creator']);
        });
    }

    /**
     * Memperbarui Transaksi Penjualan yang berstatus DRAFT.
     */
    public function updateSale(Sale $sale, array $data, array $items = [], ?int $userId = null): Sale
    {
        if (!$sale->isDraft()) {
            throw new InvalidArgumentException("Hanya penjualan berstatus DRAFT yang dapat diedit.");
        }

        if (empty($items) && !empty($data['items'])) {
            $items = $data['items'];
        }

        $userId = $userId ?? ($data['created_by'] ?? (auth()->id() ?? 1));

        if (empty($items)) {
            throw new InvalidArgumentException("Transaksi penjualan harus memiliki minimal satu item produk.");
        }

        return DB::transaction(function () use ($sale, $data, $items, $userId) {
            $saleDate = !empty($data['sale_date']) ? Carbon::parse($data['sale_date']) : $sale->sale_date;
            $warehouseId = (int) ($data['warehouse_id'] ?? $sale->warehouse_id);
            $discount = (float) ($data['discount'] ?? $sale->discount);
            $tax = (float) ($data['tax'] ?? $sale->tax);
            $shippingCost = (float) ($data['shipping_cost'] ?? $sale->shipping_cost);
            $paidAmount = (float) ($data['paid_amount'] ?? $sale->paid_amount);
            $status = $data['status'] ?? $sale->status;

            $subtotal = 0;
            $calculatedItems = [];

            foreach ($items as $item) {
                $product = Product::findOrFail($item['product_id']);
                $qty = (int) $item['quantity'];
                if ($qty <= 0) {
                    throw new InvalidArgumentException("Kuantitas produk [{$product->name}] harus lebih besar dari 0.");
                }

                $unitPrice = isset($item['unit_price']) ? (float) $item['unit_price'] : (float) $product->selling_price;
                $discountPercent = (float) ($item['discount_percent'] ?? 0);
                $taxPercent = (float) ($item['tax_percent'] ?? 0);

                $lineBase = $qty * $unitPrice;
                $lineDisc = $lineBase * ($discountPercent / 100);
                $lineAfterDisc = $lineBase - $lineDisc;
                $lineTax = $lineAfterDisc * ($taxPercent / 100);
                $itemSubtotal = max(0, $lineAfterDisc + $lineTax);

                $subtotal += $lineBase;

                $calculatedItems[] = [
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'discount_percent' => $discountPercent,
                    'tax_percent' => $taxPercent,
                    'subtotal' => $itemSubtotal,
                ];
            }

            $total = max(0, $subtotal - $discount + $tax + $shippingCost);
            $changeAmount = max(0, $paidAmount - $total);

            $sale->update([
                'warehouse_id' => $warehouseId,
                'customer_name' => $data['customer_name'] ?? $sale->customer_name,
                'customer_phone' => $data['customer_phone'] ?? $sale->customer_phone,
                'sale_date' => $saleDate,
                'status' => $status,
                'payment_method' => $data['payment_method'] ?? $sale->payment_method,
                'payment_status' => $data['payment_status'] ?? $sale->payment_status,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax' => $tax,
                'shipping_cost' => $shippingCost,
                'paid_amount' => $paidAmount,
                'change_amount' => $changeAmount,
                'total' => $total,
                'notes' => $data['notes'] ?? $sale->notes,
            ]);

            $sale->items()->delete();
            foreach ($calculatedItems as $cItem) {
                $sale->items()->create($cItem);
            }

            // If updated to COMPLETED, deduct stock
            if ($status === Sale::STATUS_COMPLETED) {
                foreach ($calculatedItems as $cItem) {
                    $note = "Penjualan Faktur #{$sale->invoice_number}" . ($sale->customer_name ? " ({$sale->customer_name})" : "");
                    $this->inventoryService->stockOut(
                        productId: $cItem['product_id'],
                        warehouseId: $warehouseId,
                        quantity: $cItem['quantity'],
                        type: InventoryTransaction::TYPE_SALE,
                        notes: $note,
                        userId: $userId,
                        refType: 'Sale',
                        referenceId: $sale->invoice_number,
                        date: $saleDate
                    );
                }
            }

            return $sale->fresh(['items.product', 'warehouse', 'creator']);
        });
    }

    /**
     * Menyelesaikan transaksi penjualan berstatus DRAFT dan memotong stok gudang.
     */
    public function completeSale(Sale $sale, ?int $userId = null): Sale
    {
        if (!$sale->isDraft()) {
            throw new InvalidArgumentException("Hanya penjualan berstatus DRAFT yang dapat diselesaikan.");
        }

        $userId = $userId ?? (auth()->id() ?? 1);

        return DB::transaction(function () use ($sale, $userId) {
            $sale->load('items');

            foreach ($sale->items as $item) {
                $note = "Penjualan Faktur #{$sale->invoice_number}" . ($sale->customer_name ? " ({$sale->customer_name})" : "");
                $this->inventoryService->stockOut(
                    productId: $item->product_id,
                    warehouseId: $sale->warehouse_id,
                    quantity: $item->quantity,
                    type: InventoryTransaction::TYPE_SALE,
                    notes: $note,
                    userId: $userId,
                    refType: 'Sale',
                    referenceId: $sale->invoice_number,
                    date: $sale->sale_date
                );
            }

            $sale->update(['status' => Sale::STATUS_COMPLETED]);
            return $sale->fresh(['items.product', 'warehouse', 'creator']);
        });
    }

    /**
     * Membatalkan transaksi penjualan.
     * Jika transaksi sebelumnya berstatus COMPLETED, stok dikembalikan ke gudang via RETURN_IN.
     */
    public function cancelSale(Sale $sale, ?string $reason = null, ?int $userId = null): Sale
    {
        if ($sale->isCancelled()) {
            throw new InvalidArgumentException("Transaksi penjualan ini sudah berstatus CANCELLED.");
        }

        return DB::transaction(function () use ($sale, $reason, $userId) {
            $sale->load('items');

            if ($sale->isCompleted()) {
                // Return stock back to warehouse
                foreach ($sale->items as $item) {
                    $note = "Pembatalan Penjualan #{$sale->invoice_number}" . ($reason ? " ({$reason})" : "");
                    $this->inventoryService->stockIn(
                        productId: $item->product_id,
                        warehouseId: $sale->warehouse_id,
                        quantity: $item->quantity,
                        type: InventoryTransaction::TYPE_RETURN_IN,
                        notes: $note,
                        userId: $userId,
                        refType: 'Sale',
                        referenceId: $sale->invoice_number,
                        date: Carbon::now()
                    );
                }
            }

            $notes = $reason
                ? ($sale->notes ? "{$sale->notes} | Dibatalkan: {$reason}" : "Dibatalkan: {$reason}")
                : $sale->notes;

            $sale->update([
                'status' => Sale::STATUS_CANCELLED,
                'notes' => $notes,
            ]);

            return $sale->fresh(['items.product', 'warehouse', 'creator']);
        });
    }
}
