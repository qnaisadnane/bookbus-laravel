<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bus extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'bus';

    protected $fillable = [
        'registration_number',
        'model',
        'type',
        'capacity',
        'available_seats',
        'wifi',
        'power_outlets',
        'toilet',
        'status',
        'last_maintenance',
        'next_maintenance',
    ];

    protected $casts = [
        'wifi' => 'boolean',
        'power_outlets' => 'boolean',
        'toilet' => 'boolean',
        'last_maintenance' => 'date',
        'next_maintenance' => 'date',
    ];

    /**
     * Get all trips assigned to this bus
     */
    public function trips(): HasMany
    {
        return $this->hasMany(Trip::class);
    }

    /**
     * Get all assignments for this bus
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    /**
     * Check if bus is available for maintenance
     */
    public function isInMaintenance(): bool
    {
        return $this->status === 'maintenance';
    }

    /**
     * Check if bus is available for service
     */
    public function isInService(): bool
    {
        return $this->status === 'in_service';
    }

    /**
     * Get premium price multiplier based on type
     */
    public function getPriceMultiplier(): float
    {
        return match ($this->type) {
            'comfort' => 1.1,
            'premium' => 1.2,
            default => 1.0,
        };
    }
}
