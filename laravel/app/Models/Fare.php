<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Fare extends Model
{
    use HasFactory;

    protected $fillable = [
        'segment_id',
        'bus_type',
        'price',
        'active',
        'effective_from',
        'effective_until',
    ];

    protected $casts = [
        'active' => 'boolean',
        'effective_from' => 'date',
        'effective_until' => 'date',
    ];

    /**
     * Get the segment this fare belongs to
     */
    public function segment(): BelongsTo
    {
        return $this->belongsTo(Segment::class);
    }

    /**
     * Check if fare is currently active
     */
    public function isActive(): bool
    {
        if (!$this->active) {
            return false;
        }

        $now = now()->toDateString();
        
        if ($this->effective_from && $this->effective_from->toDateString() > $now) {
            return false;
        }

        if ($this->effective_until && $this->effective_until->toDateString() < $now) {
            return false;
        }

        return true;
    }
}
