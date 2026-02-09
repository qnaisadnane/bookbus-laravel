<?php

namespace App\Services;

use App\Models\Route;
use App\Models\Stop;
use App\Models\Trip;
use App\Models\Schedule;
use App\Models\Segment;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class SearchService
{
    /**
     * Search for available trips
     * 
     * CRITICAL: This search only returns SATAS routes
     * Each route can have multiple stops, so passengers can travel any portion
     */
    public function searchTrips(
        string $departureStationName,
        string $arrivalStationName,
        Carbon $departureDate,
        ?string $busType = null,
        ?array $filters = []
    ): array {
        // Get departure and arrival stations
        $departureStop = Stop::with('station')
            ->whereHas('station', fn($q) => $q->where('name', $departureStationName))
            ->first();

        $arrivalStop = Stop::with('station')
            ->whereHas('station', fn($q) => $q->where('name', $arrivalStationName))
            ->first();

        if (!$departureStop || !$arrivalStop) {
            return [];
        }

        // Must be on same route
        if ($departureStop->route_id !== $arrivalStop->route_id) {
            return [];
        }

        // Departure must be before arrival
        if ($departureStop->order >= $arrivalStop->order) {
            return [];
        }

        $route = $departureStop->route;
        
        // Get segments between these two stops
        $segments = Segment::where('route_id', $route->id)
            ->where('departure_stop_id', $departureStop->id)
            ->where('arrival_stop_id', $arrivalStop->id)
            ->get();

        if ($segments->isEmpty()) {
            // Direct segment doesn't exist, build multi-segment path
            $segments = $this->getMultiSegmentPath($departureStop, $arrivalStop);
        }

        // Get schedules for departure day
        $dayOfWeek = strtolower($departureDate->format('l'));
        $schedules = Schedule::where('route_id', $route->id)
            ->where('day_of_week', $dayOfWeek)
            ->where('active', true)
            ->get();

        $results = [];

        foreach ($schedules as $schedule) {
            // Get or create trip for this date
            $trip = Trip::firstOrCreate(
                [
                    'schedule_id' => $schedule->id,
                    'departure_date' => $departureDate,
                ],
                [
                    'status' => 'scheduled',
                ]
            );

            // Skip cancelled trips
            if ($trip->status === 'cancelled') {
                continue;
            }

            // Check if bus is assigned
            if (!$trip->bus_id) {
                continue;
            }

            $bus = $trip->bus;

            // Apply bus type filter
            if ($busType && $bus->type !== $busType) {
                continue;
            }

            // Apply other filters
            if (!$this->passFilters($bus, $filters)) {
                continue;
            }

            // Calculate fare
            $fare = $this->calculateFare($segments, $bus->type);

            $results[] = [
                'trip_id' => $trip->id,
                'route' => $route,
                'bus' => $bus,
                'departure_time' => $schedule->departure_time,
                'arrival_time' => $schedule->arrival_time,
                'departure_date' => $departureDate,
                'segments' => $segments,
                'fare' => $fare,
                'available_seats' => $bus->capacity,
                'features' => [
                    'wifi' => $bus->wifi,
                    'power_outlets' => $bus->power_outlets,
                    'toilet' => $bus->toilet,
                ],
            ];
        }

        return $results;
    }

    /**
     * Get multi-segment path between two stops
     */
    private function getMultiSegmentPath(Stop $departure, Stop $arrival): Collection
    {
        $segments = collect();
        
        for ($i = $departure->order; $i < $arrival->order; $i++) {
            $currentStop = Stop::where('route_id', $departure->route_id)
                ->where('order', $i)
                ->first();

            $nextStop = Stop::where('route_id', $departure->route_id)
                ->where('order', $i + 1)
                ->first();

            if ($currentStop && $nextStop) {
                $segment = Segment::where('route_id', $departure->route_id)
                    ->where('departure_stop_id', $currentStop->id)
                    ->where('arrival_stop_id', $nextStop->id)
                    ->first();

                if ($segment) {
                    $segments->push($segment);
                }
            }
        }

        return $segments;
    }

    /**
     * Calculate total fare for segments
     * CRITICAL: Sum of segment prices, independent pricing model
     */
    public function calculateFare(Collection $segments, string $busType = 'standard'): float
    {
        $totalFare = 0;

        foreach ($segments as $segment) {
            $fare = $segment->getPriceForBusType($busType);
            if ($fare) {
                $totalFare += $fare;
            }
        }

        return $totalFare;
    }

    /**
     * Apply additional filters to bus selection
     */
    private function passFilters($bus, ?array $filters): bool
    {
        if (!$filters) {
            return true;
        }

        if (isset($filters['wifi']) && $filters['wifi'] && !$bus->wifi) {
            return false;
        }

        if (isset($filters['power_outlets']) && $filters['power_outlets'] && !$bus->power_outlets) {
            return false;
        }

        if (isset($filters['toilet']) && $filters['toilet'] && !$bus->toilet) {
            return false;
        }

        return true;
    }

    /**
     * Get price difference for premium bus types
     */
    public function getPriceSummary(array $searchResult): array
    {
        $bus = $searchResult['bus'];
        $baseFare = $searchResult['fare'];

        return [
            'base_fare' => $baseFare,
            'bus_type_multiplier' => $bus->getPriceMultiplier(),
            'final_fare' => $baseFare * $bus->getPriceMultiplier(),
        ];
    }
}
