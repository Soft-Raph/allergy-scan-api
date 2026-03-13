<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Profile extends Model
{
    protected $fillable = ['user_id', 'name'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function allergens(): BelongsToMany
    {
        return $this->belongsToMany(Allergen::class, 'profile_allergens')->withTimestamps();
    }

    public function scanLogs(): HasMany
    {
        return $this->hasMany(ScanLog::class);
    }
}