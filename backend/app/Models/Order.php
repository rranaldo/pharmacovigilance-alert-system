<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'purchase_date',
        'total',
    ];

    protected function casts(): array
    {
        return [
            'purchase_date' => 'date',
            'total' => 'decimal:2',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class);
    }

    public function scopeWithLotNumber($query, string $lotNumber)
    {
        return $query->whereHas('items.medication', function ($q) use ($lotNumber) {
            $q->where('lot_number', $lotNumber);
        });
    }

    /** Defaults to last 30 days when no range is provided. */
    public function scopeInDateRange($query, ?string $startDate = null, ?string $endDate = null)
    {
        $start = $startDate ?? now()->subDays(30)->toDateString();
        $end = $endDate ?? now()->toDateString();

        return $query->whereBetween('purchase_date', [$start, $end]);
    }
}
