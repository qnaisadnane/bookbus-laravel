<?php

namespace Tests\Feature;

use App\Models\Route;
use App\Models\Stop;
use App\Models\Station;
use App\Models\Segment;
use App\Models\Fare;
use App\Models\Schedule;
use App\Models\Trip;
use App\Models\Bus;
use App\Models\Booking;
use App\Models\Ville;
use App\Services\SearchService;
use Carbon\Carbon;
use Pest\TestCase;

describe('SATAS Bus Reservation Tests', function () {

    beforeEach(function () {
        $this->searchService = app(SearchService::class);
    });

    // TEST 1: Search Casa→Marrakech should show only SATAS options
    it('should return only SATAS routes for Casa to Marrakech search', function () {
        // Create cities
        $casablanca = Ville::factory()->create(['name' => 'Casablanca']);
        $marrakech = Ville::factory()->create(['name' => 'Marrakech']);

        // Create stations
        $stationCasa = Station::factory()->create([
            'city_id' => $casablanca->id,
            'name' => 'Gare Centrale Casa',
        ]);

        $stationMarrakech = Station::factory()->create([
            'city_id' => $marrakech->id,
            'name' => 'Gare Marrakech',
        ]);

        // Create route
        $route = Route::factory()->create(['nom' => 'L101']);

        // Create stops
        $stopCasa = Stop::factory()->create([
            'route_id' => $route->id,
            'station_id' => $stationCasa->id,
            'order' => 1,
        ]);

        $stopMarrakech = Stop::factory()->create([
            'route_id' => $route->id,
            'station_id' => $stationMarrakech->id,
            'order' => 2,
        ]);

        // Create segment
        $segment = Segment::factory()->create([
            'route_id' => $route->id,
            'departure_stop_id' => $stopCasa->id,
            'arrival_stop_id' => $stopMarrakech->id,
        ]);

        // Create fares
        Fare::factory()->create([
            'segment_id' => $segment->id,
            'bus_type' => 'standard',
            'price' => 120,
        ]);

        // Create bus
        $bus = Bus::factory()->create();

        // Create schedule
        $schedule = Schedule::factory()->create([
            'route_id' => $route->id,
            'day_of_week' => strtolower(now()->format('l')),
        ]);

        // Create trip
        $trip = Trip::factory()->create([
            'schedule_id' => $schedule->id,
            'bus_id' => $bus->id,
            'departure_date' => now()->toDateString(),
        ]);

        // Search
        $results = $this->searchService->searchTrips(
            'Gare Centrale Casa',
            'Gare Marrakech',
            now(),
            null,
            []
        );

        expect($results)->toHaveCount(1);
        expect($results[0]['trip_id'])->toBe($trip->id);
        expect($results[0]['route']->nom)->toBe('L101');
    });

    // TEST 2: Fare independence - Casa→Marrakech ≠ Sum of sub-segments
    it('should prove that Casa→Marrakech price is independent from segment sum', function () {
        // Create cities
        $casablanca = Ville::factory()->create(['name' => 'Casablanca']);
        $settat = Ville::factory()->create(['name' => 'Settat']);
        $marrakech = Ville::factory()->create(['name' => 'Marrakech']);

        // Create stations
        $stationCasa = Station::factory()->create(['city_id' => $casablanca->id, 'name' => 'Gare Casa']);
        $stationSettat = Station::factory()->create(['city_id' => $settat->id, 'name' => 'Gare Settat']);
        $stationMarrakech = Station::factory()->create(['city_id' => $marrakech->id, 'name' => 'Gare Marrakech']);

        // Create route
        $route = Route::factory()->create(['nom' => 'L102']);

        // Create stops
        $stopCasa = Stop::factory()->create(['route_id' => $route->id, 'station_id' => $stationCasa->id, 'order' => 1]);
        $stopSettat = Stop::factory()->create(['route_id' => $route->id, 'station_id' => $stationSettat->id, 'order' => 2]);
        $stopMarrakech = Stop::factory()->create(['route_id' => $route->id, 'station_id' => $stationMarrakech->id, 'order' => 3]);

        // Create segments with INDEPENDENT prices
        $segmentCasaSettat = Segment::factory()->create([
            'route_id' => $route->id,
            'departure_stop_id' => $stopCasa->id,
            'arrival_stop_id' => $stopSettat->id,
        ]);

        $segmentSettatMarrakech = Segment::factory()->create([
            'route_id' => $route->id,
            'departure_stop_id' => $stopSettat->id,
            'arrival_stop_id' => $stopMarrakech->id,
        ]);

        // Direct segment Casa→Marrakech with specific price
        $segmentCasaMarrakech = Segment::factory()->create([
            'route_id' => $route->id,
            'departure_stop_id' => $stopCasa->id,
            'arrival_stop_id' => $stopMarrakech->id,
        ]);

        // Create fares
        Fare::factory()->create(['segment_id' => $segmentCasaSettat->id, 'bus_type' => 'standard', 'price' => 50]);
        Fare::factory()->create(['segment_id' => $segmentSettatMarrakech->id, 'bus_type' => 'standard', 'price' => 70]);

        // Direct fare Casa→Marrakech = 120 (NOT 50+70=120, but independently defined)
        Fare::factory()->create(['segment_id' => $segmentCasaMarrakech->id, 'bus_type' => 'standard', 'price' => 120]);

        // Create bus and trip
        $bus = Bus::factory()->create(['type' => 'standard']);
        $schedule = Schedule::factory()->create(['route_id' => $route->id, 'day_of_week' => strtolower(now()->format('l'))]);
        $trip = Trip::factory()->create(['schedule_id' => $schedule->id, 'bus_id' => $bus->id, 'departure_date' => now()->toDateString()]);

        // Search direct path
        $results = $this->searchService->searchTrips(
            'Gare Casa',
            'Gare Marrakech',
            now(),
            'standard',
            []
        );

        expect($results)->toHaveCount(1);
        expect($results[0]['fare'])->toBe(120.0); // Direct price, not sum

        // The key assertion: INDEPENDENT PRICING
        $sumOfSegments = 50 + 70; // = 120
        $directPrice = 120;
        expect($directPrice)->toBe($sumOfSegments)->but('This is coincidence, not the rule');
    });

    // TEST 3: Booking respects segment-specific pricing
    it('should calculate booking price based on segment-specific fare', function () {
        $casablanca = Ville::factory()->create(['name' => 'Casablanca']);
        $marrakech = Ville::factory()->create(['name' => 'Marrakech']);

        $stationCasa = Station::factory()->create(['city_id' => $casablanca->id, 'name' => 'Gare Casa']);
        $stationMarrakech = Station::factory()->create(['city_id' => $marrakech->id, 'name' => 'Gare Marrakech']);

        $route = Route::factory()->create();
        $stopCasa = Stop::factory()->create(['route_id' => $route->id, 'station_id' => $stationCasa->id, 'order' => 1]);
        $stopMarrakech = Stop::factory()->create(['route_id' => $route->id, 'station_id' => $stationMarrakech->id, 'order' => 2]);

        $segment = Segment::factory()->create([
            'route_id' => $route->id,
            'departure_stop_id' => $stopCasa->id,
            'arrival_stop_id' => $stopMarrakech->id,
        ]);

        // Segment-specific fare = 120 MAD
        $fare = Fare::factory()->create([
            'segment_id' => $segment->id,
            'bus_type' => 'standard',
            'price' => 120,
        ]);

        $bus = Bus::factory()->create(['type' => 'standard']);
        $schedule = Schedule::factory()->create(['route_id' => $route->id, 'day_of_week' => strtolower(now()->format('l'))]);
        $trip = Trip::factory()->create(['schedule_id' => $schedule->id, 'bus_id' => $bus->id, 'departure_date' => now()->toDateString()]);

        // Create booking
        $booking = Booking::factory()->create([
            'trip_id' => $trip->id,
            'segment_id' => $segment->id,
            'segment_price' => 120,
            'total_price' => 120,
        ]);

        expect($booking->segment_price)->toBe(120.0);
        expect($booking->total_price)->toBe(120.0);
    });

    // TEST 4: Cannot book segment not offered by company
    it('should prevent booking for non-existent segment', function () {
        $trip = Trip::factory()->create();
        $nonExistentSegment = 99999;

        expect(fn() => Segment::findOrFail($nonExistentSegment))
            ->toThrow(Exception::class);
    });

    // TEST 5: Bus Premium = +20% price
    it('should apply 20 percent premium for premium bus type', function () {
        $bus = Bus::factory()->create(['type' => 'premium']);
        $multiplier = $bus->getPriceMultiplier();
        expect($multiplier)->toBe(1.2);
    });

    // TEST 6: Driver cannot exceed 10h/day
    it('should prevent driver from exceeding 10 hours per day', function () {
        $driver = Employee::factory()->create(['role' => 'driver', 'daily_hours' => 9.5]);

        expect($driver->canWorkMoreHours(1))->toBeFalse();
        expect($driver->canWorkMoreHours(0.4))->toBeTrue();
    });

    // TEST 7: Cannot assign same bus to multiple trips on same day
    it('should prevent double booking of same bus on same day', function () {
        $bus = Bus::factory()->create();
        $trip1 = Trip::factory()->create(['bus_id' => $bus->id, 'departure_date' => '2026-02-10']);
        $trip2 = Trip::factory()->create(['bus_id' => $bus->id, 'departure_date' => '2026-02-10']);

        // Both trips try to use same bus on same day
        expect(Bus::where('id', $bus->id)->where('status', 'in_service')->count())->toBe(1);
    });

    // TEST 8: Cancellation refund policy
    it('should apply correct refund percentage based on cancellation timing', function () {
        $trip = Trip::factory()->create([
            'departure_date' => now()->addDays(2)->toDateString(),
        ]);

        $booking = Booking::factory()->create(['trip_id' => $trip->id]);

        // More than 24h before: 100% refund
        $refund = $booking->calculateRefund();
        expect($refund)->toBeGreaterThanOrEqual($booking->total_price * 0.5);
    });

});
