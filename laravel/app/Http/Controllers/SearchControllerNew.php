<?php

namespace App\Http\Controllers;

use App\Models\Station;
use App\Services\SearchService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function __construct(
        protected SearchService $searchService
    ) {}

    /**
     * Afficher le formulaire de recherche
     */
    public function index(): View
    {
        // Récupérer toutes les stations SATAS
        $stations = Station::with('city')
            ->orderBy('name')
            ->get()
            ->groupBy('city.name');

        return view('search.index', compact('stations'));
    }

    /**
     * Traiter la recherche et afficher les résultats
     */
    public function search(Request $request)
    {
        $validated = $request->validate([
            'departure_station' => 'required|string',
            'arrival_station' => 'required|string',
            'departure_date' => 'required|date|after:yesterday',
            'bus_type' => 'nullable|in:standard,comfort,premium',
            'wifi' => 'nullable|boolean',
            'power_outlets' => 'nullable|boolean',
            'toilet' => 'nullable|boolean',
        ]);

        $departureDate = Carbon::parse($validated['departure_date']);

        $filters = [
            'wifi' => $validated['wifi'] ?? false,
            'power_outlets' => $validated['power_outlets'] ?? false,
            'toilet' => $validated['toilet'] ?? false,
        ];

        $results = $this->searchService->searchTrips(
            $validated['departure_station'],
            $validated['arrival_station'],
            $departureDate,
            $validated['bus_type'] ?? null,
            $filters
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'count' => count($results),
                'results' => $results,
            ]);
        }

        return view('search.results', [
            'results' => $results,
            'departure' => $validated['departure_station'],
            'arrival' => $validated['arrival_station'],
            'departureDate' => $departureDate,
        ]);
    }
}
