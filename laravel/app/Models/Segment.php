<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Segment extends Model
{
    use HasFactory;

    protected $fillable = [
        'route_id',
        'departure_stop_id',
        'arrival_stop_id',
        'distance_km',
        'duration_minutes',
    ];

    /**
     * Get the route this segment belongs to
     */
    public function route(): BelongsTo
    {
        return $this->belongsTo(Route::class);
    }

    /**
     * Get the departure stop
     */
    public function departureStop(): BelongsTo
    {
        return $this->belongsTo(Stop::class, 'departure_stop_id');
    }

    /**
     * Get the arrival stop
     */
    public function arrivalStop(): BelongsTo
    {
        return $this->belongsTo(Stop::class, 'arrival_stop_id');
    }

    /**
     * Get all fares for this segment
     */
    public function fares(): HasMany
    {
        return $this->hasMany(Fare::class);
    }

    /**
     * Get all bookings for this segment
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Get the current price for a specific bus type
     */
    public function getPriceForBusType(string $busType = 'standard'): ?float
    {
        $fare = $this->fares()
            ->where('bus_type', $busType)
            ->where('active', true)
            ->where(function ($query) {
                $query->whereNull('effective_from')
                    ->orWhere('effective_from', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('effective_until')
                    ->orWhere('effective_until', '>=', now());
            })
            ->latest('effective_from')
            ->first();

        return $fare?->price;
    }
}
