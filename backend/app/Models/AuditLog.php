<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'entity_type',
        'entity_id',
        'details',
        'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'details' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getEntityAttribute(): ?Model
    {
        if (!$this->entity_type || !$this->entity_id) {
            return null;
        }

        if (!class_exists($this->entity_type)) {
            return null;
        }

        return $this->entity_type::find($this->entity_id);
    }
}
