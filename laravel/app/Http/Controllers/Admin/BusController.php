<?php

namespace App\Http\Controllers\Admin;

use App\Models\Bus;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Routing\Controller;

class BusController extends Controller
{
    /**
     * List all buses
     */
    public function index(): View
    {
        $buses = Bus::with('assignments')
            ->orderBy('registration_number')
            ->paginate(15);

        return view('admin.buses.index', compact('buses'));
    }

    /**
     * Show bus details
     */
    public function show(Bus $bus): View
    {
        $bus->load(['assignments.trip', 'assignments.driver']);

        return view('admin.buses.show', compact('bus'));
    }

    /**
     * Update bus status
     */
    public function updateStatus(Request $request, Bus $bus)
    {
        $validated = $request->validate([
            'status' => 'required|in:in_service,maintenance,out_of_service',
        ]);

        if ($validated['status'] === 'maintenance') {
            $validated['last_maintenance'] = now()->toDateString();
            $validated['next_maintenance'] = now()->addMonths(3)->toDateString();
        }

        $bus->update($validated);

        return back()->with('success', 'Statut du bus mis à jour');
    }

    /**
     * Get bus statistics
     */
    public function statistics(): View
    {
        $stats = [
            'by_type' => Bus::select('type')->selectRaw('count(*) as count')->groupBy('type')->get(),
            'by_status' => Bus::select('status')->selectRaw('count(*) as count')->groupBy('status')->get(),
            'total_capacity' => Bus::sum('capacity'),
            'in_maintenance' => Bus::where('status', 'maintenance')->count(),
        ];

        $buses = Bus::with('assignments')
            ->select(['id', 'registration_number', 'type', 'capacity', 'status'])
            ->get();

        return view('admin.buses.statistics', compact('stats', 'buses'));
    }
}
