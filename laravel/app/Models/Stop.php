<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Stop extends Model
{
    use HasFactory;

    protected $fillable = [
        'route_id',
        'station_id',
        'order',
        'duration_minutes',
    ];

    /**
     * Get the route this stop belongs to
     */
    public function route(): BelongsTo
    {
        return $this->belongsTo(Route::class);
    }

    /**
     * Get the station
     */
    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class);
    }

    /**
     * Get all segments starting from this stop
     */
    public function departureSegments(): HasMany
    {
        return $this->hasMany(Segment::class, 'departure_stop_id');
    }

    /**
     * Get all segments ending at this stop
     */
    public function arrivalSegments(): HasMany
    {
        return $this->hasMany(Segment::class, 'arrival_stop_id');
    }
}
