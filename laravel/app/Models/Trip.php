<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Trip extends Model
{
    use HasFactory;

    protected $fillable = [
        'schedule_id',
        'bus_id',
        'departure_date',
        'actual_departure_time',
        'actual_arrival_time',
        'status',
        'cancellation_reason',
        'available_seats',
    ];

    protected $casts = [
        'departure_date' => 'date',
        'actual_departure_time' => 'datetime',
        'actual_arrival_time' => 'datetime',
    ];

    /**
     * Get the schedule
     */
    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    /**
     * Get the bus
     */
    public function bus(): BelongsTo
    {
        return $this->belongsTo(Bus::class);
    }

    /**
     * Get all assignments for this trip
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    /**
     * Get all bookings for this trip
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Get the driver assigned to this trip
     */
    public function driver()
    {
        return $this->hasOneThrough(
            Employee::class,
            Assignment::class,
            'trip_id',
            'id',
            'id',
            'driver_id'
        );
    }

    /**
     * Check if trip can be cancelled (> 24h before departure)
     */
    public function canBeCancelled(): bool
    {
        $hoursUntilDeparture = now()->diffInHours($this->schedule->route->departure_time, false);
        return $hoursUntilDeparture > 24;
    }

    /**
     * Get cancellation refund percentage
     */
    public function getRefundPercentage(): int
    {
        $hoursUntilDeparture = now()->diffInHours($this->schedule->route->departure_time, false);
        
        if ($hoursUntilDeparture > 24) {
            return 100; // Full refund
        }
        
        return 50; // 50% refund if cancelled < 24h before
    }
}
