<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScanLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'profile_id',
        'product_id',
        'rating',
        'triggered_allergens',
        'created_at',
    ];

    protected $casts = [
        'triggered_allergens' => 'array',
        'created_at'          => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}