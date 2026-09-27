<?php

namespace App\Services;

use App\Exceptions\InsufficientStockException;
use App\Models\InventoryStock;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class InventoryService
{
    /**
     * Menambah stok barang masuk (Stock In) secara atomik.
     */
    public function stockIn(
        int $productId,
        int $warehouseId,
        int $quantity,
        string $type = InventoryTransaction::TYPE_PURCHASE,
        ?string $notes = null,
        ?int $userId = null,
        ?string $refType = null,
        ?string $referenceId = null,
        ?Carbon $date = null
    ): InventoryTransaction {
        if ($quantity <= 0) {
            throw new InvalidArgumentException("Kuantitas barang masuk harus lebih besar dari 0.");
        }

        $this->validateProductAndWarehouse($productId, $warehouseId);
        $transactionDate = $date ?? Carbon::now();

        return DB::transaction(function () use (
            $productId,
            $warehouseId,
            $quantity,
            $type,
            $notes,
            $userId,
            $refType,
            $referenceId,
            $transactionDate
        ) {
            /** @var InventoryStock $stock */
            $stock = InventoryStock::where('product_id', $productId)
                ->where('warehouse_id', $warehouseId)
                ->lockForUpdate()
                ->first();

            if (!$stock) {
                $stock = InventoryStock::create([
                    'product_id' => $productId,
                    'warehouse_id' => $warehouseId,
                    'quantity' => 0,
                    'reserved_quantity' => 0,
                ]);
            }

            $stockBefore = $stock->quantity;
            $stockAfter = $stockBefore + $quantity;

            $stock->update(['quantity' => $stockAfter]);

            return InventoryTransaction::create([
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'transaction_type' => $type,
                'quantity' => $quantity,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'reference_type' => $refType,
                'reference_id' => $referenceId,
                'notes' => $notes,
                'performed_by' => $userId,
                'transaction_date' => $transactionDate,
            ]);
        });
    }

    /**
     * Mengurangi stok barang keluar (Stock Out) secara atomik dengan validasi kecukupan stok.
     */
    public function stockOut(
        int $productId,
        int $warehouseId,
        int $quantity,
        string $type = InventoryTransaction::TYPE_SALE,
        ?string $notes = null,
        ?int $userId = null,
        ?string $refType = null,
        ?string $referenceId = null,
        ?Carbon $date = null
    ): InventoryTransaction {
        if ($quantity <= 0) {
            throw new InvalidArgumentException("Kuantitas barang keluar harus lebih besar dari 0.");
        }

        $this->validateProductAndWarehouse($productId, $warehouseId);
        $transactionDate = $date ?? Carbon::now();

        return DB::transaction(function () use (
            $productId,
            $warehouseId,
            $quantity,
            $type,
            $notes,
            $userId,
            $refType,
            $referenceId,
            $transactionDate
        ) {
            /** @var InventoryStock|null $stock */
            $stock = InventoryStock::where('product_id', $productId)
                ->where('warehouse_id', $warehouseId)
                ->lockForUpdate()
                ->first();

            $available = $stock ? $stock->available_quantity : 0;

            if ($available < $quantity) {
                throw new InsufficientStockException(
                    "Stok di gudang tidak mencukupi untuk dikeluarkan. Stok tersedia: {$available}, dibutuhkan: {$quantity}."
                );
            }

            $stockBefore = $stock->quantity;
            $stockAfter = $stockBefore - $quantity;

            $stock->update(['quantity' => $stockAfter]);

            return InventoryTransaction::create([
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'transaction_type' => $type,
                'quantity' => $quantity,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'reference_type' => $refType,
                'reference_id' => $referenceId,
                'notes' => $notes,
                'performed_by' => $userId,
                'transaction_date' => $transactionDate,
            ]);
        });
    }

    /**
     * Memindahkan stok antar-gudang secara atomik (Transfer Stock).
     * Menghasilkan record TRANSFER_OUT di gudang asal dan TRANSFER_IN di gudang tujuan.
     */
    public function transfer(
        int $productId,
        int $fromWarehouseId,
        int $toWarehouseId,
        int $quantity,
        ?string $notes = null,
        ?int $userId = null,
        ?string $refType = null,
        ?string $referenceId = null,
        ?Carbon $date = null
    ): array {
        if ($fromWarehouseId === $toWarehouseId) {
            throw new InvalidArgumentException("Gudang asal dan gudang tujuan transfer tidak boleh sama.");
        }

        if ($quantity <= 0) {
            throw new InvalidArgumentException("Kuantitas transfer barang harus lebih besar dari 0.");
        }

        $this->validateProductAndWarehouse($productId, $fromWarehouseId);
        $this->validateProductAndWarehouse($productId, $toWarehouseId);
        $transactionDate = $date ?? Carbon::now();

        return DB::transaction(function () use (
            $productId,
            $fromWarehouseId,
            $toWarehouseId,
            $quantity,
            $notes,
            $userId,
            $refType,
            $referenceId,
            $transactionDate
        ) {
            // 1. Lock source warehouse stock
            /** @var InventoryStock|null $sourceStock */
            $sourceStock = InventoryStock::where('product_id', $productId)
                ->where('warehouse_id', $fromWarehouseId)
                ->lockForUpdate()
                ->first();

            $sourceAvailable = $sourceStock ? $sourceStock->available_quantity : 0;
            if ($sourceAvailable < $quantity) {
                throw new InsufficientStockException(
                    "Stok di gudang asal tidak mencukupi untuk transfer. Stok tersedia: {$sourceAvailable}, akan ditransfer: {$quantity}."
                );
            }

            $sourceBefore = $sourceStock->quantity;
            $sourceAfter = $sourceBefore - $quantity;
            $sourceStock->update(['quantity' => $sourceAfter]);

            $fromWarehouse = Warehouse::find($fromWarehouseId);
            $toWarehouse = Warehouse::find($toWarehouseId);

            $outNotes = $notes ? "Transfer ke {$toWarehouse->name}: {$notes}" : "Transfer ke {$toWarehouse->name}";
            $inNotes = $notes ? "Transfer dari {$fromWarehouse->name}: {$notes}" : "Transfer dari {$fromWarehouse->name}";

            $outTransaction = InventoryTransaction::create([
                'product_id' => $productId,
                'warehouse_id' => $fromWarehouseId,
                'transaction_type' => InventoryTransaction::TYPE_TRANSFER_OUT,
                'quantity' => $quantity,
                'stock_before' => $sourceBefore,
                'stock_after' => $sourceAfter,
                'reference_type' => $refType,
                'reference_id' => $referenceId,
                'notes' => $outNotes,
                'performed_by' => $userId,
                'transaction_date' => $transactionDate,
            ]);

            // 2. Lock / create destination warehouse stock
            /** @var InventoryStock $destStock */
            $destStock = InventoryStock::where('product_id', $productId)
                ->where('warehouse_id', $toWarehouseId)
                ->lockForUpdate()
                ->first();

            if (!$destStock) {
                $destStock = InventoryStock::create([
                    'product_id' => $productId,
                    'warehouse_id' => $toWarehouseId,
                    'quantity' => 0,
                    'reserved_quantity' => 0,
                ]);
            }

            $destBefore = $destStock->quantity;
            $destAfter = $destBefore + $quantity;
            $destStock->update(['quantity' => $destAfter]);

            $inTransaction = InventoryTransaction::create([
                'product_id' => $productId,
                'warehouse_id' => $toWarehouseId,
                'transaction_type' => InventoryTransaction::TYPE_TRANSFER_IN,
                'quantity' => $quantity,
                'stock_before' => $destBefore,
                'stock_after' => $destAfter,
                'reference_type' => $refType,
                'reference_id' => $referenceId,
                'notes' => $inNotes,
                'performed_by' => $userId,
                'transaction_date' => $transactionDate,
            ]);

            return [
                'out' => $outTransaction,
                'in' => $inTransaction,
                'from_stock' => $sourceStock,
                'to_stock' => $destStock,
            ];
        });
    }

    /**
     * Menyesuaikan stok fisik riil (Stock Adjustment / Opname).
     */
    public function adjust(
        int $productId,
        int $warehouseId,
        int $actualQuantity,
        string $reason = 'Penyesuaian stok fisik',
        ?int $userId = null,
        ?string $refType = null,
        ?string $referenceId = null,
        ?Carbon $date = null
    ): ?InventoryTransaction {
        if ($actualQuantity < 0) {
            throw new InvalidArgumentException("Kuantitas fisik aktual tidak boleh bernilai negatif.");
        }

        $this->validateProductAndWarehouse($productId, $warehouseId);
        $transactionDate = $date ?? Carbon::now();

        return DB::transaction(function () use (
            $productId,
            $warehouseId,
            $actualQuantity,
            $reason,
            $userId,
            $refType,
            $referenceId,
            $transactionDate
        ) {
            /** @var InventoryStock $stock */
            $stock = InventoryStock::where('product_id', $productId)
                ->where('warehouse_id', $warehouseId)
                ->lockForUpdate()
                ->first();

            if (!$stock) {
                $stock = InventoryStock::create([
                    'product_id' => $productId,
                    'warehouse_id' => $warehouseId,
                    'quantity' => 0,
                    'reserved_quantity' => 0,
                ]);
            }

            $stockBefore = $stock->quantity;

            if ($stockBefore === $actualQuantity) {
                return null; // Tidak ada perbedaan kuantitas
            }

            if ($actualQuantity > $stockBefore) {
                $diff = $actualQuantity - $stockBefore;
                $stockAfter = $actualQuantity;
                $type = InventoryTransaction::TYPE_ADJUSTMENT_IN;
            } else {
                $diff = $stockBefore - $actualQuantity;
                $stockAfter = $actualQuantity;
                $type = InventoryTransaction::TYPE_ADJUSTMENT_OUT;
            }

            $stock->update(['quantity' => $stockAfter]);

            return InventoryTransaction::create([
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'transaction_type' => $type,
                'quantity' => $diff,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'reference_type' => $refType,
                'reference_id' => $referenceId,
                'notes' => $reason,
                'performed_by' => $userId,
                'transaction_date' => $transactionDate,
            ]);
        });
    }

    /**
     * Mendapatkan record stok barang pada gudang tertentu.
     */
    public function getStock(int $productId, int $warehouseId): InventoryStock
    {
        return InventoryStock::firstOrCreate(
            ['product_id' => $productId, 'warehouse_id' => $warehouseId],
            ['quantity' => 0, 'reserved_quantity' => 0]
        );
    }

    /**
     * Mendapatkan total stok barang di seluruh gudang.
     */
    public function getTotalStock(int $productId): int
    {
        return (int) InventoryStock::where('product_id', $productId)->sum('quantity');
    }

    /**
     * Mendapatkan riwayat kartu stok pergerakan barang secara kronologis.
     */
    public function getStockCard(
        int $productId,
        ?int $warehouseId = null,
        ?Carbon $startDate = null,
        ?Carbon $endDate = null,
        ?string $type = null
    ) {
        $query = InventoryTransaction::with(['product.unit', 'warehouse', 'user'])
            ->where('product_id', $productId);

        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }

        if ($startDate) {
            $query->whereDate('transaction_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('transaction_date', '<=', $endDate);
        }

        if ($type) {
            $query->where('transaction_type', $type);
        }

        return $query->orderBy('transaction_date', 'asc')->orderBy('id', 'asc')->get();
    }

    private function validateProductAndWarehouse(int $productId, int $warehouseId): void
    {
        $productExists = Product::where('id', $productId)->whereNull('deleted_at')->exists();
        if (!$productExists) {
            throw new InvalidArgumentException("Produk dengan ID {$productId} tidak ditemukan.");
        }

        $warehouseExists = Warehouse::where('id', $warehouseId)->whereNull('deleted_at')->exists();
        if (!$warehouseExists) {
            throw new InvalidArgumentException("Gudang dengan ID {$warehouseId} tidak ditemukan.");
        }
    }
}
