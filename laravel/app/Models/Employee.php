<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'role',
        'driver_license',
        'driver_license_expiry',
        'daily_hours',
        'status',
    ];

    protected $casts = [
        'driver_license_expiry' => 'date',
        'daily_hours' => 'float',
    ];

    /**
     * Get all assignments for this driver
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class, 'driver_id');
    }

    /**
     * Get full name
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Check if driver license is valid
     */
    public function isLicenseValid(): bool
    {
        return $this->role !== 'driver' || ($this->driver_license_expiry && $this->driver_license_expiry->isFuture());
    }

    /**
     * Check if driver can work more hours today (max 10h)
     */
    public function canWorkMoreHours(float $additionalHours = 1): bool
    {
        return ($this->daily_hours + $additionalHours) <= 10;
    }

    /**
     * Reset daily hours (should be called at midnight)
     */
    public function resetDailyHours(): void
    {
        $this->update(['daily_hours' => 0]);
    }
}
