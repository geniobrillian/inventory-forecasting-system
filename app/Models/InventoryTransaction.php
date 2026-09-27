<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryTransaction extends Model
{
    use HasFactory;

    // Transaction Type Constants
    public const TYPE_PURCHASE = 'PURCHASE';
    public const TYPE_SALE = 'SALE';
    public const TYPE_TRANSFER_IN = 'TRANSFER_IN';
    public const TYPE_TRANSFER_OUT = 'TRANSFER_OUT';
    public const TYPE_ADJUSTMENT_IN = 'ADJUSTMENT_IN';
    public const TYPE_ADJUSTMENT_OUT = 'ADJUSTMENT_OUT';
    public const TYPE_RETURN_IN = 'RETURN_IN';
    public const TYPE_RETURN_OUT = 'RETURN_OUT';
    public const TYPE_INITIAL_STOCK = 'INITIAL_STOCK';

    protected $fillable = [
        'product_id',
        'warehouse_id',
        'transaction_type',
        'quantity',
        'stock_before',
        'stock_after',
        'reference_type',
        'reference_id',
        'notes',
        'performed_by',
        'transaction_date',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'stock_before' => 'integer',
            'stock_after' => 'integer',
            'transaction_date' => 'datetime',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    public function performer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    /**
     * Memeriksa apakah transaksi ini menambah kuantitas stok.
     */
    public function isStockIncrement(): bool
    {
        return in_array($this->transaction_type, [
            self::TYPE_PURCHASE,
            self::TYPE_TRANSFER_IN,
            self::TYPE_ADJUSTMENT_IN,
            self::TYPE_RETURN_IN,
            self::TYPE_INITIAL_STOCK,
        ]);
    }
}
