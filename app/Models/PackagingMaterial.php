<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackagingMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'price',
        'description',
        'icon',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'float',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc')->orderBy('price', 'asc');
    }

    /**
     * Get array representation matching the catalog specification.
     */
    public function toCatalogArray(): array
    {
        return [
            'id' => $this->code,
            'code' => $this->code,
            'name' => $this->name,
            'price' => (float) $this->price,
            'description' => $this->description ?? '',
            'icon' => $this->icon ?? 'box',
            'is_active' => $this->is_active,
        ];
    }
}
