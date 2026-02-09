<?php

namespace App\Http\Controllers\Admin;

use App\Models\Trip;
use App\Models\Assignment;
use App\Models\Bus;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Routing\Controller;

class TripController extends Controller
{
    /**
     * List all trips
     */
    public function index(): View
    {
        $trips = Trip::with(['schedule.route', 'bus', 'assignments.driver'])
            ->orderBy('departure_date', 'desc')
            ->paginate(15);

        return view('admin.trips.index', compact('trips'));
    }

    /**
     * Show trip details
     */
    public function show(Trip $trip): View
    {
        $trip->load(['schedule.route', 'bus', 'assignments.driver', 'bookings.passengers']);

        return view('admin.trips.show', compact('trip'));
    }

    /**
     * Assign bus and driver to trip
     */
    public function assignResources(Request $request, Trip $trip)
    {
        $validated = $request->validate([
            'bus_id' => 'required|exists:buses,id',
            'driver_id' => 'required|exists:employees,id',
        ]);

        $bus = Bus::findOrFail($validated['bus_id']);
        $driver = Employee::findOrFail($validated['driver_id']);

        // Validate driver license
        if (!$driver->isLicenseValid()) {
            return back()->with('error', 'Le permis du chauffeur n\'est pas valide');
        }

        // Check if bus is already assigned to another trip on same day
        $conflictingAssignment = Assignment::whereHas('trip', function ($q) use ($trip, $bus) {
            $q->where('departure_date', $trip->departure_date)
              ->where('id', '!=', $trip->id);
        })
        ->where('bus_id', $bus->id)
        ->exists();

        if ($conflictingAssignment) {
            return back()->with('error', 'Ce bus est déjà assigné à un autre trajet ce jour');
        }

        // Create assignment
        Assignment::create([
            'trip_id' => $trip->id,
            'bus_id' => $bus->id,
            'driver_id' => $driver->id,
            'status' => 'confirmed',
        ]);

        // Update trip bus
        $trip->update(['bus_id' => $bus->id]);

        return back()->with('success', 'Bus et chauffeur assignés avec succès');
    }

    /**
     * Cancel a trip
     */
    public function cancel(Request $request, Trip $trip)
    {
        if ($trip->status === 'cancelled') {
            return back()->with('error', 'Ce trajet a déjà été annulé');
        }

        $trip->update([
            'status' => 'cancelled',
            'cancellation_reason' => $request->reason ?? 'Annulation administrative',
        ]);

        // Calculate refunds for all bookings
        foreach ($trip->bookings as $booking) {
            $refund = $booking->calculateRefund();
            // Send refund notifications
        }

        return back()->with('success', 'Trajet annulé, remboursements en cours');
    }
}
