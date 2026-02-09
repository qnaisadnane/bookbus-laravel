<?php

namespace App\Http\Controllers\Admin;

use App\Models\Trip;
use App\Models\Assignment;
use App\Models\Bus;
use App\Models\Employee;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Routing\Controller;

class DashboardController extends Controller
{
    /**
     * Show admin dashboard
     */
    public function index(): View
    {
        $stats = [
            'total_buses' => Bus::count(),
            'buses_in_service' => Bus::where('status', 'in_service')->count(),
            'buses_in_maintenance' => Bus::where('status', 'maintenance')->count(),
            'total_drivers' => Employee::where('role', 'driver')->count(),
            'active_drivers' => Employee::where('role', 'driver')->where('status', 'active')->count(),
            'total_trips_today' => Trip::where('departure_date', now()->toDateString())->count(),
            'completed_trips' => Trip::where('status', 'completed')->count(),
            'cancelled_trips' => Trip::where('status', 'cancelled')->count(),
        ];

        $upcomingTrips = Trip::with(['schedule.route', 'bus', 'assignments.driver'])
            ->where('departure_date', '>=', now()->toDateString())
            ->where('status', '!=', 'cancelled')
            ->orderBy('departure_date')
            ->limit(10)
            ->get();

        $busStats = Bus::with('assignments')
            ->select(['id', 'registration_number', 'type', 'status'])
            ->get()
            ->map(function ($bus) {
                return [
                    'id' => $bus->id,
                    'registration' => $bus->registration_number,
                    'type' => $bus->type,
                    'status' => $bus->status,
                    'active_trips' => $bus->assignments()->count(),
                ];
            });

        return view('admin.dashboard', compact('stats', 'upcomingTrips', 'busStats'));
    }
}
