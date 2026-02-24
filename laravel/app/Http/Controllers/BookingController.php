<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Passenger;
use App\Models\Trip;
use App\Models\Segment;
use App\Models\Fare;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BookingController extends Controller
{
    /**
     * Show booking form for a specific trip and segment
     */
    public function create(Request $request): View
    {
        $tripId = $request->trip_id;
        $segmentId = $request->segment_id;
        $nombreVoyageurs = $request->nombre_voyageurs ?? 1;

        $trip = Trip::with(['schedule.route', 'bus', 'assignments.driver'])
            ->findOrFail($tripId);

        $segment = Segment::with(['departureStop.station', 'arrivalStop.station'])
            ->findOrFail($segmentId);

        // Get fare for this segment and bus type (with fallback)
        $busType = $trip->bus->type ?? 'standard';
        $fare = Fare::where('segment_id', $segmentId)
            ->where('bus_type', $busType)
            ->where('active', true)
            ->first();

        // Fallback: try standard fare
        if (!$fare) {
            $fare = Fare::where('segment_id', $segmentId)
                ->where('bus_type', 'standard')
                ->where('active', true)
                ->first();
        }

        // Last resort: any active fare for this segment
        if (!$fare) {
            $fare = Fare::where('segment_id', $segmentId)
                ->where('active', true)
                ->first();
        }

        if (!$fare) {
            abort(404, 'Tarif non disponible pour ce trajet');
        }

        // Check seat availability
        $bookedSeats = Booking::where('trip_id', $tripId)
            ->where('segment_id', $segmentId)
            ->count();

        $availableSeats = $trip->bus->capacity - $bookedSeats;

        if ($availableSeats <= 0) {
            return back()->with('error', 'Aucune place disponible pour ce trajet');
        }

        // Verify we have enough seats for the requested number of passengers
        if ($availableSeats < $nombreVoyageurs) {
            return back()->with('error', "Seulement {$availableSeats} places disponibles pour ce trajet");
        }

        return view('booking.create', compact(
            'trip',
            'segment',
            'fare',
            'availableSeats',
            'nombreVoyageurs'
        ));
    }

    /**
     * Store a new booking
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'trip_id'                  => 'required|exists:trips,id',
            'segment_id'               => 'required|exists:segments,id',
            'passengers'               => 'required|array|min:1',
            'passengers.*.first_name'  => 'required|string|max:255',
            'passengers.*.last_name'   => 'required|string|max:255',
            'passengers.*.email'       => 'required|email',
            'passengers.*.phone'       => 'required|string|max:20',
        ]);

        $trip = Trip::findOrFail($validated['trip_id']);
        $segment = Segment::findOrFail($validated['segment_id']);
        $busType = $trip->bus->type ?? 'standard';
        $fare = Fare::where('segment_id', $segment->id)
            ->where('bus_type', $busType)
            ->where('active', true)
            ->first();

        // Fallback to standard or any fare
        if (!$fare) {
            $fare = Fare::where('segment_id', $segment->id)
                ->where('active', true)
                ->first();
        }

        if (!$fare) {
            abort(404, 'Tarif non disponible pour ce trajet');
        }

        // Calculate price
        $segmentPrice = $fare->price;
        $totalPrice = $segmentPrice * count($validated['passengers']);

        $insurancePrice = 0;
        $snackboxPrice  = 0;
        $discountAmount = 0;
        $insurance      = $validated['insurance'] ?? 'none';

        // Add insurance if selected
        if ($insurance !== 'none') {
            $insurancePrice = match ($insurance) {
                'partial' => $totalPrice * 0.05,
                'full'    => $totalPrice * 0.08,
                default   => 0,
            };
            $totalPrice += $insurancePrice;
        }

        // Add snack box if selected (15 MAD per passenger)
        if ($request->boolean('snackbox')) {
            $snackboxPrice = 15 * count($validated['passengers']);
            $totalPrice   += $snackboxPrice;
        }

        // Apply promo code discount if provided
        if ($request->filled('promo_code')) {
            $discount = $this->validatePromoCode($request->promo_code);
            if ($discount) {
                $discountAmount = $totalPrice * ($discount / 100);
                $totalPrice    -= $discountAmount;
            }
        }

        // Create booking
        $booking = DB::transaction(function () use ($validated, $trip, $segment, $segmentPrice, $totalPrice, $insurancePrice, $snackboxPrice, $discountAmount) {
            $booking = Booking::create([
                'user_id' => auth()->id() ?? 1, // For now, use default user
                'trip_id' => $validated['trip_id'],
                'segment_id' => $validated['segment_id'],
                'status' => 'confirmed',
                'segment_price' => $segmentPrice,
                'total_price' => $totalPrice,
                'discount_amount' => $discountAmount,
                'promo_code' => $validated['promo_code'] ?? null,
                'insurance' => $validated['insurance'] ?? 'none',
                'insurance_price' => $insurancePrice,
                'snackbox_price' => $snackboxPrice,
                'booked_at' => now(),
            ]);

            // Create passengers
            $seatNumber = 1;
            foreach ($validated['passengers'] as $passengerData) {
                Passenger::create([
                    'booking_id' => $booking->id,
                    'first_name' => $passengerData['first_name'],
                    'last_name' => $passengerData['last_name'],
                    'email' => $passengerData['email'],
                    'phone' => $passengerData['phone'],
                    'id_document' => $passengerData['id_document'] ?? null,
                    'seat_number' => $seatNumber++,
                ]);
            }

            return $booking;
        });

        return redirect()->route('booking.confirmation', $booking->id)
            ->with('success', 'Réservation confirmée avec succès!');
    }

    /**
     * Show booking confirmation
     */
    public function confirmation(Booking $booking): View
    {
        $booking->load([
            'trip.schedule.route',
            'trip.bus',
            'trip.assignments.driver',
            'segment.departureStop.station',
            'segment.arrivalStop.station',
            'passengers'
        ]);

        return view('booking.confirmation', compact('booking'));
    }

    /**
     * Show booking details
     */
    public function show(Booking $booking): View
    {
        $booking->load([
            'trip.schedule.route',
            'trip.bus',
            'segment',
            'passengers'
        ]);

        return view('booking.show', compact('booking'));
    }

    /**
     * Cancel a booking
     */
    public function cancel(Request $request, Booking $booking)
    {
        if ($booking->status === 'cancelled') {
            return back()->with('error', 'Cette réservation a déjà été annulée');
        }

        // Check if booking can be cancelled
        if (!$booking->canBeCancelled()) {
            return back()->with('error', 'Les réservations ne peuvent être annulées que 24h avant le départ');
        }

        $refundAmount = $booking->calculateRefund();

        // Update booking status
        $booking->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => $request->reason ?? 'Demande d\'annulation client',
        ]);

        return back()->with('success', "Réservation annulée. Remboursement: {$refundAmount} MAD");
    }

    /**
     * Validate promo code
     */
    private function validatePromoCode(string $promoCode): ?int
    {
        // Simple promo code validation
        $promoCodes = [
            'SATAS10' => 10,  // 10% discount
            'SATAS15' => 15,  // 15% discount
            'SATAS20' => 20,  // 20% discount
            'LOYALTY5' => 5,  // 5% loyalty discount
        ];

        return $promoCodes[$promoCode] ?? null;
    }

    /**
     * Get seat availability for a trip
     */
    public function getSeatMap(Request $request)
    {
        $tripId = $request->query('trip_id');
        $segmentId = $request->query('segment_id');

        $trip = Trip::findOrFail($tripId);
        $totalSeats = $trip->bus->capacity;

        // Get booked seats for this segment
        $bookedSeats = Passenger::whereHas('booking', function ($q) use ($tripId, $segmentId) {
            $q->where('trip_id', $tripId)
              ->where('segment_id', $segmentId);
        })->pluck('seat_number')->toArray();

        $seatMap = [];
        $rows = ceil($totalSeats / 4); // 4 seats per row
        $seatNumber = 1;

        for ($row = 1; $row <= $rows; $row++) {
            $seatMap[$row] = [];
            for ($col = 1; $col <= 4; $col++) {
                if ($seatNumber <= $totalSeats) {
                    $seatMap[$row][$col] = [
                        'number' => $seatNumber,
                        'booked' => in_array($seatNumber, $bookedSeats),
                    ];
                    $seatNumber++;
                }
            }
        }

        return response()->json([
            'total_seats' => $totalSeats,
            'booked_seats' => count($bookedSeats),
            'available_seats' => $totalSeats - count($bookedSeats),
            'seat_map' => $seatMap,
        ]);
    }
}
