<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Purchase extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_DRAFT = 'DRAFT';
    public const STATUS_ORDERED = 'ORDERED';
    public const STATUS_PARTIALLY_RECEIVED = 'PARTIALLY_RECEIVED';
    public const STATUS_RECEIVED = 'RECEIVED';
    public const STATUS_CANCELLED = 'CANCELLED';

    protected $fillable = [
        'purchase_number',
        'supplier_id',
        'warehouse_id',
        'status',
        'purchase_date',
        'expected_date',
        'subtotal',
        'discount',
        'tax',
        'total',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'purchase_date' => 'date',
            'expected_date' => 'date',
            'subtotal' => 'decimal:2',
            'discount' => 'decimal:2',
            'tax' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isOrdered(): bool
    {
        return $this->status === self::STATUS_ORDERED;
    }

    public function isPartiallyReceived(): bool
    {
        return $this->status === self::STATUS_PARTIALLY_RECEIVED;
    }

    public function isReceived(): bool
    {
        return $this->status === self::STATUS_RECEIVED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function canEdit(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_ORDERED]);
    }

    public function canReceive(): bool
    {
        return in_array($this->status, [self::STATUS_ORDERED, self::STATUS_PARTIALLY_RECEIVED]);
    }

    public function canCancel(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_ORDERED]);
    }

    public function getTotalQuantityAttribute(): int
    {
        return (int) $this->items->sum('quantity');
    }

    public function getTotalReceivedQuantityAttribute(): int
    {
        return (int) $this->items->sum('received_quantity');
    }

    public function getReceivingProgressPercentAttribute(): int
    {
        $total = $this->total_quantity;
        if ($total <= 0) {
            return 0;
        }

        return min(100, (int) round(($this->total_received_quantity / $total) * 100));
    }
}
