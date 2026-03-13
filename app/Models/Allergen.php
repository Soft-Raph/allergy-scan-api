<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Allergen extends Model
{
    protected $fillable = ['slug', 'name', 'description', 'keywords'];

    protected $casts = [
        'keywords' => 'array',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_allergens')->withTimestamps();
    }

    public function profiles(): BelongsToMany
    {
        return $this->belongsToMany(Profile::class, 'profile_allergens')->withTimestamps();
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_allergen')->withPivot('type');
    }
}