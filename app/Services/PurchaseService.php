<?php

namespace App\Services;

use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PurchaseService
{
    public function __construct(
        private readonly InventoryService $inventoryService
    ) {}

    /**
     * Generate Nomor Purchase Order unik berurutan. (e.g. PO-202609-0001)
     */
    public function generatePurchaseNumber(): string
    {
        $prefix = 'PO-' . date('Ym') . '-';
        $latest = Purchase::withTrashed()
            ->where('purchase_number', 'like', "{$prefix}%")
            ->orderByDesc('id')
            ->value('purchase_number');

        if (!$latest) {
            return $prefix . '0001';
        }

        $number = (int) substr($latest, strlen($prefix)) + 1;
        return $prefix . str_pad((string) $number, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Membuat Purchase Order baru beserta item produknya.
     */
    public function createPurchase(array $data, array $items = [], ?int $userId = null): Purchase
    {
        if (empty($items) && !empty($data['items'])) {
            $items = $data['items'];
        }

        $userId = $userId ?? ($data['created_by'] ?? 1);

        if (empty($items)) {
            throw new InvalidArgumentException("Purchase Order harus memiliki minimal satu item produk.");
        }

        return DB::transaction(function () use ($data, $items, $userId) {
            $purchaseNumber = $data['purchase_number'] ?? $this->generatePurchaseNumber();
            $purchaseDate = !empty($data['purchase_date']) ? Carbon::parse($data['purchase_date']) : Carbon::now();
            $expectedDate = !empty($data['expected_date']) ? Carbon::parse($data['expected_date']) : null;
            $discount = (float) ($data['discount'] ?? 0);
            $tax = (float) ($data['tax'] ?? 0);
            $status = $data['status'] ?? Purchase::STATUS_DRAFT;

            $subtotal = 0;
            $calculatedItems = [];

            foreach ($items as $item) {
                $product = Product::findOrFail($item['product_id']);
                $qty = (int) $item['quantity'];
                if ($qty <= 0) {
                    throw new InvalidArgumentException("Kuantitas produk [{$product->name}] harus lebih besar dari 0.");
                }

                $unitCost = isset($item['unit_cost']) ? (float) $item['unit_cost'] : (float) $product->purchase_price;
                $itemSubtotal = $qty * $unitCost;
                $subtotal += $itemSubtotal;

                $calculatedItems[] = [
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'received_quantity' => 0,
                    'unit_cost' => $unitCost,
                    'subtotal' => $itemSubtotal,
                ];
            }

            $total = max(0, $subtotal - $discount + $tax);

            /** @var Purchase $purchase */
            $purchase = Purchase::create([
                'purchase_number' => $purchaseNumber,
                'supplier_id' => $data['supplier_id'],
                'warehouse_id' => $data['warehouse_id'],
                'status' => $status,
                'purchase_date' => $purchaseDate,
                'expected_date' => $expectedDate,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax' => $tax,
                'total' => $total,
                'notes' => $data['notes'] ?? null,
                'created_by' => $userId,
            ]);

            foreach ($calculatedItems as $cItem) {
                $purchase->items()->create($cItem);
            }

            return $purchase->load(['items.product', 'supplier', 'warehouse', 'creator']);
        });
    }

    /**
     * Mengubah Purchase Order yang masih berstatus DRAFT atau ORDERED.
     */
    public function updatePurchase(Purchase $purchase, array $data, array $items): Purchase
    {
        if (!$purchase->canEdit()) {
            throw new InvalidArgumentException("Purchase Order dengan status {$purchase->status} tidak dapat diubah.");
        }

        if (empty($items)) {
            throw new InvalidArgumentException("Purchase Order harus memiliki minimal satu item produk.");
        }

        return DB::transaction(function () use ($purchase, $data, $items) {
            $purchaseDate = !empty($data['purchase_date']) ? Carbon::parse($data['purchase_date']) : $purchase->purchase_date;
            $expectedDate = !empty($data['expected_date']) ? Carbon::parse($data['expected_date']) : null;
            $discount = (float) ($data['discount'] ?? $purchase->discount);
            $tax = (float) ($data['tax'] ?? $purchase->tax);

            $subtotal = 0;
            $calculatedItems = [];

            foreach ($items as $item) {
                $product = Product::findOrFail($item['product_id']);
                $qty = (int) $item['quantity'];
                if ($qty <= 0) {
                    throw new InvalidArgumentException("Kuantitas produk [{$product->name}] harus lebih besar dari 0.");
                }

                $unitCost = isset($item['unit_cost']) ? (float) $item['unit_cost'] : (float) $product->purchase_price;
                $itemSubtotal = $qty * $unitCost;
                $subtotal += $itemSubtotal;

                $calculatedItems[] = [
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'received_quantity' => 0,
                    'unit_cost' => $unitCost,
                    'subtotal' => $itemSubtotal,
                ];
            }

            $total = max(0, $subtotal - $discount + $tax);

            $purchase->update([
                'supplier_id' => $data['supplier_id'] ?? $purchase->supplier_id,
                'warehouse_id' => $data['warehouse_id'] ?? $purchase->warehouse_id,
                'purchase_date' => $purchaseDate,
                'expected_date' => $expectedDate,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax' => $tax,
                'total' => $total,
                'notes' => $data['notes'] ?? $purchase->notes,
            ]);

            // Replace items safely
            $purchase->items()->delete();
            foreach ($calculatedItems as $cItem) {
                $purchase->items()->create($cItem);
            }

            return $purchase->fresh(['items.product', 'supplier', 'warehouse', 'creator']);
        });
    }

    /**
     * Memperbarui status PO dari DRAFT menjadi ORDERED.
     */
    public function orderPurchase(Purchase $purchase): Purchase
    {
        if (!$purchase->isDraft()) {
            throw new InvalidArgumentException("Hanya Purchase Order berstatus DRAFT yang dapat diajukan pemesanan.");
        }

        $purchase->update(['status' => Purchase::STATUS_ORDERED]);
        return $purchase;
    }

    /**
     * Memproses penerimaan fisik barang (Receiving) dan memicu Stock In otomatis.
     */
    public function receiveItems(
        Purchase $purchase,
        array $receivedItems,
        ?string $notes = null,
        ?int $userId = null,
        ?Carbon $receivingDate = null
    ): Purchase {
        if (!$purchase->canReceive()) {
            throw new InvalidArgumentException("Purchase Order dengan status {$purchase->status} tidak dapat menerima barang.");
        }

        if (isset($receivedItems['items']) && is_array($receivedItems['items'])) {
            $notes = $notes ?? ($receivedItems['notes'] ?? null);
            if (!empty($receivedItems['received_date'])) {
                $receivingDate = Carbon::parse($receivedItems['received_date']);
            }
            $receivedItems = $receivedItems['items'];
        }

        $userId = $userId ?? (auth()->id() ?? 1);
        $txDate = $receivingDate ?? Carbon::now();

        return DB::transaction(function () use ($purchase, $receivedItems, $notes, $userId, $txDate) {
            $totalReceivedInBatch = 0;

            foreach ($receivedItems as $rec) {
                $purchaseItemId = (int) $rec['purchase_item_id'];
                $qtyToReceive = (int) $rec['received_quantity'];

                if ($qtyToReceive <= 0) {
                    continue;
                }

                /** @var PurchaseItem $item */
                $item = $purchase->items()->where('id', $purchaseItemId)->firstOrFail();

                if ($qtyToReceive > $item->remaining_quantity) {
                    throw new InvalidArgumentException(
                        "Kuantitas penerimaan ({$qtyToReceive}) untuk produk [{$item->product->name}] melebihi sisa pesanan ({$item->remaining_quantity})."
                    );
                }

                // 1. Update received quantity on purchase item
                $item->increment('received_quantity', $qtyToReceive);
                $totalReceivedInBatch += $qtyToReceive;

                // 2. Trigger atomic Stock In in the warehouse
                $itemNotes = $notes
                    ? "Penerimaan PO #{$purchase->purchase_number}: {$notes}"
                    : "Penerimaan PO #{$purchase->purchase_number}";

                $this->inventoryService->stockIn(
                    productId: $item->product_id,
                    warehouseId: $purchase->warehouse_id,
                    quantity: $qtyToReceive,
                    type: InventoryTransaction::TYPE_PURCHASE,
                    notes: $itemNotes,
                    userId: $userId,
                    refType: 'Purchase',
                    referenceId: $purchase->purchase_number,
                    date: $txDate
                );
            }

            if ($totalReceivedInBatch <= 0) {
                throw new InvalidArgumentException("Harus ada minimal satu item dengan kuantitas penerimaan lebih besar dari 0.");
            }

            // 3. Determine new PO status (PARTIALLY_RECEIVED or RECEIVED)
            $purchase->load('items');
            $allFullyReceived = $purchase->items->every(fn (PurchaseItem $i) => $i->isFullyReceived());

            $newStatus = $allFullyReceived ? Purchase::STATUS_RECEIVED : Purchase::STATUS_PARTIALLY_RECEIVED;
            $purchase->update(['status' => $newStatus]);

            return $purchase->fresh(['items.product', 'supplier', 'warehouse', 'creator']);
        });
    }

    /**
     * Membatalkan Purchase Order (hanya jika belum ada barang yang diterima).
     */
    public function cancelPurchase(Purchase $purchase, ?string $reason = null): Purchase
    {
        if (!$purchase->canCancel()) {
            throw new InvalidArgumentException("Purchase Order dengan status {$purchase->status} tidak dapat dibatalkan.");
        }

        $note = $reason ? ($purchase->notes ? "{$purchase->notes} | Dibatalkan: {$reason}" : "Dibatalkan: {$reason}") : $purchase->notes;

        $purchase->update([
            'status' => Purchase::STATUS_CANCELLED,
            'notes' => $note,
        ]);

        return $purchase;
    }
}
