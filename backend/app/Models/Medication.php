<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Medication extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'lot_number',
        'expiration_date',
    ];

    protected function casts(): array
    {
        return [
            'expiration_date' => 'date',
        ];
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function scopeByLot($query, string $lotNumber)
    {
        return $query->where('lot_number', $lotNumber);
    }
}
