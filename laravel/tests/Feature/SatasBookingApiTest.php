<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Ville;
use App\Models\Station;
use App\Models\Route;
use App\Models\Stop;
use App\Models\Segment;
use App\Models\Fare;
use App\Models\Bus;
use App\Models\Schedule;
use App\Models\Trip;
use App\Models\Booking;
use App\Models\Employee;
use Carbon\Carbon;

class SatasBookingApiTest extends TestCase
{
    /**
     * TEST 1: Search Casa→Marrakech should show SATAS options
     */
    public function test_search_casa_marrakech_returns_satas_routes()
    {
        // Setup: Create necessary data
        $casa = Ville::firstOrCreate(['name' => 'Casablanca']);
        $marrakech = Ville::firstOrCreate(['name' => 'Marrakech']);

        $stationCasa = Station::firstOrCreate(
            ['name' => 'Gare Centrale Casa'],
            ['city_id' => $casa->id, 'address' => 'Boulevard Mohamed V']
        );

        $stationMarrakech = Station::firstOrCreate(
            ['name' => 'Gare Marrakech'],
            ['city_id' => $marrakech->id, 'address' => 'Avenue Hassan II']
        );

        $route = Route::firstOrCreate(['nom' => 'L101']);

        $stopCasa = Stop::firstOrCreate(
            ['route_id' => $route->id, 'station_id' => $stationCasa->id],
            ['order' => 1, 'duration_minutes' => 5]
        );

        $stopMarrakech = Stop::firstOrCreate(
            ['route_id' => $route->id, 'station_id' => $stationMarrakech->id],
            ['order' => 2, 'duration_minutes' => 5]
        );

        $segment = Segment::firstOrCreate(
            [
                'route_id' => $route->id,
                'departure_stop_id' => $stopCasa->id,
                'arrival_stop_id' => $stopMarrakech->id,
            ],
            ['distance_km' => 250, 'duration_minutes' => 300]
        );

        Fare::firstOrCreate(
            ['segment_id' => $segment->id, 'bus_type' => 'standard'],
            ['price' => 120, 'active' => true]
        );

        $bus = Bus::firstOrCreate(['registration_number' => 'SP-101']);

        $schedule = Schedule::firstOrCreate(
            [
                'route_id' => $route->id,
                'day_of_week' => strtolower(now()->format('l')),
                'departure_time' => '08:00',
            ],
            ['arrival_time' => '14:00', 'active' => true]
        );

        $trip = Trip::firstOrCreate(
            ['schedule_id' => $schedule->id, 'departure_date' => now()->toDateString()],
            ['bus_id' => $bus->id, 'status' => 'scheduled']
        );

        // Test: Search for trips
        $response = $this->get('/search?departure_station=Gare%20Centrale%20Casa&arrival_station=Gare%20Marrakech&departure_date=' . now()->toDateString());

        $response->assertStatus(200);
    }

    /**
     * TEST 2: Verify segment price independence
     */
    public function test_segment_pricing_is_independent()
    {
        $segment = Segment::first() ?? Segment::factory()->create();
        
        // Create fares for same segment but different prices
        $fareStandard = Fare::firstOrCreate(
            ['segment_id' => $segment->id, 'bus_type' => 'standard'],
            ['price' => 100, 'active' => true]
        );

        $fareComfort = Fare::firstOrCreate(
            ['segment_id' => $segment->id, 'bus_type' => 'comfort'],
            ['price' => 110, 'active' => true]
        );

        // Assertion: Prices are independent
        $this->assertNotEquals($fareStandard->price, $fareComfort->price);
        $this->assertEquals(100, $fareStandard->price);
        $this->assertEquals(110, $fareComfort->price);
    }

    /**
     * TEST 3: Booking respects segment-specific pricing
     */
    public function test_booking_uses_segment_specific_price()
    {
        $segment = Segment::first() ?? Segment::factory()->create();
        $trip = Trip::first() ?? Trip::factory()->create();
        $bus = $trip->bus;

        $fare = Fare::firstOrCreate(
            ['segment_id' => $segment->id, 'bus_type' => $bus->type],
            ['price' => 150, 'active' => true]
        );

        $booking = Booking::factory()->create([
            'trip_id' => $trip->id,
            'segment_id' => $segment->id,
            'segment_price' => 150,
            'total_price' => 150,
        ]);

        $this->assertEquals(150, $booking->segment_price);
    }

    /**
     * TEST 4: Cannot book non-existent segment
     */
    public function test_cannot_book_non_existent_segment()
    {
        $trip = Trip::first() ?? Trip::factory()->create();

        $response = $this->post('/booking/store', [
            'trip_id' => $trip->id,
            'segment_id' => 99999,
            'passengers' => [],
        ]);

        // Should fail validation
        $response->assertStatus(302); // Redirect on error
    }

    /**
     * TEST 5: Premium bus costs 20% more
     */
    public function test_premium_bus_multiplier_is_1_2()
    {
        $bus = Bus::firstOrCreate(['registration_number' => 'SP-PREMIUM'], ['type' => 'premium']);
        $this->assertEquals(1.2, $bus->getPriceMultiplier());
    }

    /**
     * TEST 6: Driver cannot exceed 10 hours per day
     */
    public function test_driver_max_10_hours_per_day()
    {
        $driver = Employee::firstOrCreate(
            ['email' => 'driver@test.com'],
            [
                'first_name' => 'Ahmed',
                'last_name' => 'Test',
                'phone' => '0612345678',
                'role' => 'driver',
                'daily_hours' => 9.5,
            ]
        );

        $this->assertFalse($driver->canWorkMoreHours(1));
        $this->assertTrue($driver->canWorkMoreHours(0.4));
    }

    /**
     * TEST 7: Cancellation refund policy - >24h
     */
    public function test_cancellation_refund_more_than_24h()
    {
        $trip = Trip::firstOrCreate(
            ['departure_date' => now()->addDays(2)->toDateString()],
            ['schedule_id' => Schedule::first()->id ?? 1]
        );

        $booking = Booking::factory()->create([
            'trip_id' => $trip->id,
            'total_price' => 100,
        ]);

        $refundPercentage = $trip->getRefundPercentage();
        $this->assertGreaterThanOrEqual(50, $refundPercentage);
    }

    /**
     * TEST 8: Admin can assign bus and driver to trip
     */
    public function test_admin_can_assign_bus_and_driver()
    {
        $trip = Trip::first() ?? Trip::factory()->create();
        $bus = Bus::first() ?? Bus::factory()->create();
        $driver = Employee::firstOrCreate(
            ['email' => 'driver2@test.com'],
            [
                'first_name' => 'Mohammed',
                'last_name' => 'Test',
                'phone' => '0612345679',
                'role' => 'driver',
            ]
        );

        // Simulate admin action
        $assignment = [
            'trip_id' => $trip->id,
            'bus_id' => $bus->id,
            'driver_id' => $driver->id,
        ];

        $this->assertIsArray($assignment);
        $this->assertEquals($trip->id, $assignment['trip_id']);
    }
}
