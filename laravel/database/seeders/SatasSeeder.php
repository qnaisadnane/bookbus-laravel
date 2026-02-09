<?php

namespace Database\Seeders;

use App\Models\Ville;
use App\Models\Station;
use App\Models\Route;
use App\Models\Stop;
use App\Models\Segment;
use App\Models\Fare;
use App\Models\Bus;
use App\Models\Employee;
use App\Models\Schedule;
use App\Models\Trip;
use App\Models\Assignment;
use Illuminate\Database\Seeder;

class SatasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create cities
        $cities = [
            'Casablanca' => ['lat' => 33.5731, 'lng' => -7.5898],
            'Marrakech' => ['lat' => 31.6295, 'lng' => -8.0088],
            'Fez' => ['lat' => 34.0181, 'lng' => -5.0078],
            'Tangier' => ['lat' => 35.7595, 'lng' => -5.8330],
            'Agadir' => ['lat' => 30.4278, 'lng' => -9.5982],
            'Settat' => ['lat' => 33.0058, 'lng' => -7.6169],
            'Meknes' => ['lat' => 33.8869, 'lng' => -5.5511],
            'Tanger' => ['lat' => 35.7595, 'lng' => -5.8330],
        ];

        $villeModels = [];
        foreach ($cities as $cityName => $coords) {
            $villeModels[$cityName] = Ville::create(['name' => $cityName]);
        }

        // Create stations
        $stationsData = [
            'Casablanca' => [
                ['name' => 'Gare Centrale Casa', 'address' => 'Boulevard Mohamed V'],
                ['name' => 'Gare Peripherie Casa', 'address' => 'Route de Marrakech'],
            ],
            'Marrakech' => [
                ['name' => 'Gare Marrakech', 'address' => 'Avenue Mohamed VI'],
                ['name' => 'Gare Medina Marrakech', 'address' => 'Rue Jemaa El Fnaa'],
            ],
            'Settat' => [
                ['name' => 'Gare Settat', 'address' => 'Centre Ville'],
            ],
            'Fez' => [
                ['name' => 'Gare Fez', 'address' => 'Boulevard Hassan II'],
            ],
            'Tangier' => [
                ['name' => 'Gare Tangier', 'address' => 'Boulevard Pasteur'],
            ],
            'Agadir' => [
                ['name' => 'Gare Agadir', 'address' => 'Avenue Hassan II'],
            ],
            'Meknes' => [
                ['name' => 'Gare Meknes', 'address' => 'Centre Ville'],
            ],
        ];

        $stationModels = [];
        foreach ($stationsData as $cityName => $stations) {
            foreach ($stations as $stationData) {
                $station = Station::create([
                    'city_id' => $villeModels[$cityName]->id,
                    'name' => $stationData['name'],
                    'address' => $stationData['address'],
                    'latitude' => $cities[$cityName]['lat'],
                    'longitude' => $cities[$cityName]['lng'],
                ]);
                $stationModels[$stationData['name']] = $station;
            }
        }

        // Create routes (10 SATAS lines)
        $routesData = [
            ['nom' => 'L101', 'description' => 'Casa - Marrakech Express', 'departure' => 'Gare Centrale Casa', 'arrival' => 'Gare Marrakech'],
            ['nom' => 'L102', 'description' => 'Casa - Agadir', 'departure' => 'Gare Centrale Casa', 'arrival' => 'Gare Agadir'],
            ['nom' => 'L103', 'description' => 'Casa - Fez Express', 'departure' => 'Gare Centrale Casa', 'arrival' => 'Gare Fez'],
            ['nom' => 'L104', 'description' => 'Casa - Settat', 'departure' => 'Gare Centrale Casa', 'arrival' => 'Gare Settat'],
            ['nom' => 'L105', 'description' => 'Marrakech - Agadir', 'departure' => 'Gare Marrakech', 'arrival' => 'Gare Agadir'],
            ['nom' => 'L106', 'description' => 'Fez - Tangier', 'departure' => 'Gare Fez', 'arrival' => 'Gare Tangier'],
            ['nom' => 'L107', 'description' => 'Meknes - Fez', 'departure' => 'Gare Meknes', 'arrival' => 'Gare Fez'],
            ['nom' => 'L108', 'description' => 'Casa - Tangier', 'departure' => 'Gare Centrale Casa', 'arrival' => 'Gare Tangier'],
            ['nom' => 'L109', 'description' => 'Agadir - Essaouira', 'departure' => 'Gare Agadir', 'arrival' => 'Gare Marrakech'],
            ['nom' => 'L110', 'description' => 'Marrakech - Fez', 'departure' => 'Gare Marrakech', 'arrival' => 'Gare Fez'],
        ];

        $routeModels = [];
        foreach ($routesData as $routeData) {
            $route = Route::create([
                'nom' => $routeData['nom'],
                'description' => $routeData['description'],
            ]);
            $routeModels[$routeData['nom']] = [
                'route' => $route,
                'departure' => $routeData['departure'],
                'arrival' => $routeData['arrival'],
            ];
        }

        // Create stops for each route
        $routeStops = [
            'L101' => ['Gare Centrale Casa', 'Gare Peripherie Casa', 'Gare Settat', 'Gare Marrakech'],
            'L102' => ['Gare Centrale Casa', 'Gare Settat', 'Gare Marrakech', 'Gare Agadir'],
            'L103' => ['Gare Centrale Casa', 'Gare Settat', 'Gare Meknes', 'Gare Fez'],
            'L104' => ['Gare Centrale Casa', 'Gare Settat'],
            'L105' => ['Gare Marrakech', 'Gare Medina Marrakech', 'Gare Agadir'],
            'L106' => ['Gare Fez', 'Gare Meknes', 'Gare Tangier'],
            'L107' => ['Gare Meknes', 'Gare Fez'],
            'L108' => ['Gare Centrale Casa', 'Gare Settat', 'Gare Meknes', 'Gare Fez', 'Gare Tangier'],
            'L109' => ['Gare Agadir', 'Gare Marrakech'],
            'L110' => ['Gare Marrakech', 'Gare Medina Marrakech', 'Gare Settat', 'Gare Meknes', 'Gare Fez'],
        ];

        foreach ($routeStops as $routeName => $stopNames) {
            $route = $routeModels[$routeName]['route'];
            foreach ($stopNames as $index => $stopName) {
                Stop::create([
                    'route_id' => $route->id,
                    'station_id' => $stationModels[$stopName]->id,
                    'order' => $index + 1,
                    'duration_minutes' => 5,
                ]);
            }
        }

        // Create segments and fares for each route
        foreach ($routeStops as $routeName => $stopNames) {
            $route = $routeModels[$routeName]['route'];
            
            for ($i = 0; $i < count($stopNames) - 1; $i++) {
                $departureStop = Stop::where('route_id', $route->id)->where('order', $i + 1)->first();
                $arrivalStop = Stop::where('route_id', $route->id)->where('order', $i + 2)->first();

                if ($departureStop && $arrivalStop) {
                    $segment = Segment::create([
                        'route_id' => $route->id,
                        'departure_stop_id' => $departureStop->id,
                        'arrival_stop_id' => $arrivalStop->id,
                        'distance_km' => rand(50, 300),
                        'duration_minutes' => rand(60, 480),
                    ]);

                    // Create fares for each bus type
                    $basePrices = ['standard' => rand(50, 150), 'comfort' => rand(80, 180), 'premium' => rand(100, 200)];
                    foreach ($basePrices as $busType => $basePrice) {
                        Fare::create([
                            'segment_id' => $segment->id,
                            'bus_type' => $busType,
                            'price' => $basePrice,
                            'active' => true,
                        ]);
                    }
                }
            }
        }

        // Create 20 SATAS buses
        $busRegistrations = [
            'SP-101', 'SP-102', 'SP-103', 'SP-104', 'SP-105',
            'SP-106', 'SP-107', 'SP-108', 'SP-109', 'SP-110',
            'SP-201', 'SP-202', 'SP-203', 'SP-204', 'SP-205',
            'SP-206', 'SP-207', 'SP-208', 'SP-209', 'SP-210',
        ];

        $busModels = [];
        foreach ($busRegistrations as $index => $registration) {
            $busType = match ($index % 3) {
                0 => 'standard',
                1 => 'comfort',
                default => 'premium',
            };

            $bus = Bus::create([
                'registration_number' => $registration,
                'model' => 'Mercedes-Benz ' . ['Sprinter', 'Tourismo', 'Actros'][$index % 3],
                'type' => $busType,
                'capacity' => match ($busType) {
                    'standard' => 40,
                    'comfort' => 35,
                    'premium' => 30,
                },
                'available_seats' => match ($busType) {
                    'standard' => 40,
                    'comfort' => 35,
                    'premium' => 30,
                },
                'wifi' => $busType !== 'standard',
                'power_outlets' => $busType === 'premium',
                'toilet' => true,
                'status' => 'in_service',
            ]);
            $busModels[] = $bus;
        }

        // Create 15 SATAS drivers
        $drivers = [
            ['first_name' => 'Ahmed', 'last_name' => 'Ben Salem', 'email' => 'ahmed.ben@satas.ma'],
            ['first_name' => 'Mohamed', 'last_name' => 'Al Mansouri', 'email' => 'mohamed.al@satas.ma'],
            ['first_name' => 'Hassan', 'last_name' => 'Bouazza', 'email' => 'hassan.bz@satas.ma'],
            ['first_name' => 'Ali', 'last_name' => 'Bennani', 'email' => 'ali.bn@satas.ma'],
            ['first_name' => 'Khalid', 'last_name' => 'Rizki', 'email' => 'khalid.rz@satas.ma'],
            ['first_name' => 'Abdelhadi', 'last_name' => 'Saouri', 'email' => 'abdelhadi.sr@satas.ma'],
            ['first_name' => 'Nabil', 'last_name' => 'Bennani', 'email' => 'nabil.bn@satas.ma'],
            ['first_name' => 'Omar', 'last_name' => 'El Hadj', 'email' => 'omar.eh@satas.ma'],
            ['first_name' => 'Fatima', 'last_name' => 'Zahra', 'email' => 'fatima.z@satas.ma'],
            ['first_name' => 'Hafsa', 'last_name' => 'Mouhssen', 'email' => 'hafsa.m@satas.ma'],
            ['first_name' => 'Karim', 'last_name' => 'Belmoussa', 'email' => 'karim.bm@satas.ma'],
            ['first_name' => 'Samir', 'last_name' => 'Bennani', 'email' => 'samir.bn@satas.ma'],
            ['first_name' => 'Jamal', 'last_name' => 'Bennani', 'email' => 'jamal.bn@satas.ma'],
            ['first_name' => 'Rashid', 'last_name' => 'Lakhal', 'email' => 'rashid.lh@satas.ma'],
            ['first_name' => 'Ibrahim', 'last_name' => 'Bennani', 'email' => 'ibrahim.bn@satas.ma'],
        ];

        $driverModels = [];
        foreach ($drivers as $driver) {
            $employee = Employee::create([
                'first_name' => $driver['first_name'],
                'last_name' => $driver['last_name'],
                'email' => $driver['email'],
                'phone' => '+212' . rand(600000000, 799999999),
                'role' => 'driver',
                'driver_license' => 'DL-' . rand(1000000, 9999999),
                'driver_license_expiry' => now()->addYears(3),
                'status' => 'active',
            ]);
            $driverModels[] = $employee;
        }

        // Create schedules
        $daysOfWeek = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        $departureTimes = ['06:00', '08:00', '10:00', '12:00', '14:00', '16:00', '18:00', '20:00'];

        foreach ($routeModels as $routeName => $routeData) {
            $route = $routeData['route'];
            foreach ($daysOfWeek as $day) {
                foreach ($departureTimes as $time) {
                    Schedule::create([
                        'route_id' => $route->id,
                        'departure_time' => $time,
                        'arrival_time' => date('H:i', strtotime($time) + rand(3600, 28800)), // 1-8 hours later
                        'day_of_week' => $day,
                        'active' => true,
                    ]);
                }
            }
        }

        // Create trips for the next 7 days with bus assignments
        $startDate = now()->startOfDay();
        
        foreach ($routeModels as $routeName => $routeData) {
            $route = $routeData['route'];
            $schedules = Schedule::where('route_id', $route->id)->get();
            
            for ($daysAhead = 0; $daysAhead < 7; $daysAhead++) {
                $date = $startDate->copy()->addDays($daysAhead);
                $dayOfWeek = strtolower($date->format('l'));
                
                foreach ($schedules as $schedule) {
                    if ($schedule->day_of_week === $dayOfWeek) {
                        // Create trip
                        $trip = Trip::create([
                            'schedule_id' => $schedule->id,
                            'departure_date' => $date->format('Y-m-d'),
                            'bus_id' => $busModels[array_rand($busModels)]->id,
                            'status' => 'scheduled',
                        ]);
                        
                        // Create assignment with a driver
                        Assignment::create([
                            'trip_id' => $trip->id,
                            'bus_id' => $trip->bus_id,
                            'driver_id' => $driverModels[array_rand($driverModels)]->id,
                            'status' => 'assigned',
                        ]);
                    }
                }
            }
        }
    }
}
