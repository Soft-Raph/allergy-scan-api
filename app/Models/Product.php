<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'barcode',
        'name',
        'brand',
        'image_url',
        'ingredients_text',
        'upc_data',
        'fetched_at',
    ];

    protected $casts = [
        'upc_data'   => 'array',
        'fetched_at' => 'datetime',
    ];

    public function allergens(): BelongsToMany
    {
        return $this->belongsToMany(Allergen::class, 'product_allergen')->withPivot('type');
    }

    public function scanLogs(): HasMany
    {
        return $this->hasMany(ScanLog::class);
    }
}