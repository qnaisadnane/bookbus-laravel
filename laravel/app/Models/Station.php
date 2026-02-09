<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Station extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'city_id',
        'address',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    /**
     * Get the city this station belongs to
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(Ville::class, 'city_id');
    }

    /**
     * Get all stops at this station
     */
    public function stops(): HasMany
    {
        return $this->hasMany(Stop::class);
    }
}
