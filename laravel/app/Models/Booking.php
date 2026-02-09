<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Booking extends Model
{
    use HasFactory;

    protected $table = 'bookings';

    protected $fillable = [
        'user_id',
        'trip_id',
        'segment_id',
        'status',
        'segment_price',
        'total_price',
        'discount_amount',
        'promo_code',
        'insurance',
        'insurance_price',
        'snackbox_price',
        'booked_at',
        'cancelled_at',
        'cancellation_reason',
    ];

    protected $casts = [
        'booked_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'segment_price' => 'float',
        'total_price' => 'float',
        'discount_amount' => 'float',
        'insurance_price' => 'float',
        'snackbox_price' => 'float',
    ];

    /**
     * Get the user who made the booking
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the trip
     */
    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }

    /**
     * Get the segment
     */
    public function segment(): BelongsTo
    {
        return $this->belongsTo(Segment::class);
    }

    /**
     * Get all passengers for this booking
     */
    public function passengers(): HasMany
    {
        return $this->hasMany(Passenger::class);
    }

    /**
     * Calculate refund amount based on cancellation policy
     */
    public function calculateRefund(): float
    {
        $refundPercentage = $this->trip->getRefundPercentage();
        return $this->total_price * ($refundPercentage / 100);
    }

    /**
     * Apply loyalty points discount
     */
    public function applyLoyaltyDiscount(float $points): void
    {
        // 1 point = 0.1 MAD
        $discount = $points * 0.1;
        $this->discount_amount = min($discount, $this->total_price);
        $this->total_price -= $this->discount_amount;
        $this->save();
    }

    /**
     * Check if booking can be cancelled (> 24h before departure)
     */
    public function canBeCancelled(): bool
    {
        return $this->trip->canBeCancelled();
    }
}
