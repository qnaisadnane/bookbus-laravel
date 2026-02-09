<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Route extends Model
{
    use HasFactory;

    protected $table = 'route';

    protected $fillable = [
        'nom',
        'description',
    ];

    /**
     * Get all stops for this route
     */
    public function stops(): HasMany
    {
        return $this->hasMany(Stop::class)->orderBy('order');
    }

    /**
     * Get all segments for this route
     */
    public function segments(): HasMany
    {
        return $this->hasMany(Segment::class);
    }

    /**
     * Get all schedules for this route
     */
    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }

    /**
     * Get all programmes (deprecated, for backward compatibility)
     */
    public function programme(): HasMany
    {
        return $this->hasMany(Programme::class);
    }

    /**
     * Get all etapes (deprecated, for backward compatibility)
     */
    public function etape(): HasMany
    {
        return $this->hasMany(Etape::class);
    }

    /**
     * Get departure and arrival stops
     */
    public function getDepartureStop()
    {
        return $this->stops()->orderBy('order')->first();
    }

    public function getArrivalStop()
    {
        return $this->stops()->orderBy('order', 'desc')->first();
    }
}
