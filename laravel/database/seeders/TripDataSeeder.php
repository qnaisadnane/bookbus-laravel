<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TripDataSeeder extends Seeder
{
    public function run()
    {
        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            // Clean new tables
            DB::table('fares')->truncate();
            DB::table('trips')->truncate();
            DB::table('schedules')->truncate();
            DB::table('segments')->truncate();
            DB::table('stops')->truncate();
            DB::table('stations')->truncate();
            DB::table('bus')->truncate();
            DB::table('ville')->truncate();

            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            // ===== 1. VILLES =====
            $villeIds = [];
            $villes = ['Casablanca', 'Marrakech', 'Rabat', 'Fès', 'Tanger'];
            foreach ($villes as $v) {
                $villeIds[$v] = DB::table('ville')->insertGetId(['name' => $v]);
            }

            // ===== 2. STATIONS (nouvelles tables) =====
            $stationIds = [];
            $stationsData = [
                'Casablanca' => ['name' => 'Gare Ouled Ziane', 'address' => 'Route Ouled Ziane, Casablanca'],
                'Marrakech'  => ['name' => 'Gare Bab Doukkala', 'address' => 'Bab Doukkala, Marrakech'],
                'Rabat'      => ['name' => 'Gare de Rabat', 'address' => 'Avenue Hassan II, Rabat'],
                'Fès'        => ['name' => 'Gare de Fès', 'address' => 'Route Principale, Fès'],
                'Tanger'     => ['name' => 'Gare de Tanger', 'address' => 'Avenue Mohamed V, Tanger'],
            ];
            foreach ($stationsData as $ville => $data) {
                $stationIds[$ville] = DB::table('stations')->insertGetId([
                    'name'       => $data['name'],
                    'city_id'    => $villeIds[$ville],
                    'address'    => $data['address'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // ===== 3. BUS =====
            $busIds = [];
            $busData = [
                ['registration_number' => '1234-A-01', 'model' => 'Mercedes Citaro', 'type' => 'standard', 'capacity' => 50, 'wifi' => 0, 'power_outlets' => 0, 'toilet' => 0, 'status' => 'in_service'],
                ['registration_number' => '5678-B-02', 'model' => 'Scania Touring', 'type' => 'standard', 'capacity' => 48, 'wifi' => 0, 'power_outlets' => 1, 'toilet' => 0, 'status' => 'in_service'],
                ['registration_number' => '9012-C-03', 'model' => 'Volvo 9700', 'type' => 'standard', 'capacity' => 45, 'wifi' => 1, 'power_outlets' => 1, 'toilet' => 1, 'status' => 'in_service'],
            ];
            foreach ($busData as $bus) {
                $busIds[] = DB::table('bus')->insertGetId(array_merge($bus, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }

            // ===== 4. ROUTES =====
            // Route 1: Casablanca -> Marrakech
            $routeCasaMrk = DB::table('route')->insertGetId([
                'nom' => 'Ligne 101',
                'description' => 'Express Casablanca - Marrakech',
            ]);

            // Route 2: Rabat -> Casablanca
            $routeRabatCasa = DB::table('route')->insertGetId([
                'nom' => 'Ligne 102',
                'description' => 'Rabat - Casablanca',
            ]);

            // Route 3: Casablanca -> Tanger
            $routeCasaTanger = DB::table('route')->insertGetId([
                'nom' => 'Ligne 103',
                'description' => 'Casablanca - Tanger',
            ]);

            // ===== 5. STOPS (Arrêts sur chaque ligne) =====
            // Route 1: Casablanca (1) -> Marrakech (2)
            $stop_casa_r1 = DB::table('stops')->insertGetId([
                'route_id'         => $routeCasaMrk,
                'station_id'       => $stationIds['Casablanca'],
                'order'            => 1,
                'duration_minutes' => 0,
                'created_at' => now(), 'updated_at' => now(),
            ]);
            $stop_mrk_r1 = DB::table('stops')->insertGetId([
                'route_id'         => $routeCasaMrk,
                'station_id'       => $stationIds['Marrakech'],
                'order'            => 2,
                'duration_minutes' => 0,
                'created_at' => now(), 'updated_at' => now(),
            ]);

            // Route 2: Rabat (1) -> Casablanca (2)
            $stop_rabat_r2 = DB::table('stops')->insertGetId([
                'route_id'         => $routeRabatCasa,
                'station_id'       => $stationIds['Rabat'],
                'order'            => 1,
                'duration_minutes' => 0,
                'created_at' => now(), 'updated_at' => now(),
            ]);
            $stop_casa_r2 = DB::table('stops')->insertGetId([
                'route_id'         => $routeRabatCasa,
                'station_id'       => $stationIds['Casablanca'],
                'order'            => 2,
                'duration_minutes' => 0,
                'created_at' => now(), 'updated_at' => now(),
            ]);

            // Route 3: Casablanca (1) -> Tanger (2)
            $stop_casa_r3 = DB::table('stops')->insertGetId([
                'route_id'         => $routeCasaTanger,
                'station_id'       => $stationIds['Casablanca'],
                'order'            => 1,
                'duration_minutes' => 0,
                'created_at' => now(), 'updated_at' => now(),
            ]);
            $stop_tanger_r3 = DB::table('stops')->insertGetId([
                'route_id'         => $routeCasaTanger,
                'station_id'       => $stationIds['Tanger'],
                'order'            => 2,
                'duration_minutes' => 0,
                'created_at' => now(), 'updated_at' => now(),
            ]);

            // ===== 6. SEGMENTS =====
            $seg_casa_mrk = DB::table('segments')->insertGetId([
                'route_id'           => $routeCasaMrk,
                'departure_stop_id'  => $stop_casa_r1,
                'arrival_stop_id'    => $stop_mrk_r1,
                'distance_km'        => 240,
                'duration_minutes'   => 240,
                'created_at' => now(), 'updated_at' => now(),
            ]);

            $seg_rabat_casa = DB::table('segments')->insertGetId([
                'route_id'           => $routeRabatCasa,
                'departure_stop_id'  => $stop_rabat_r2,
                'arrival_stop_id'    => $stop_casa_r2,
                'distance_km'        => 87,
                'duration_minutes'   => 90,
                'created_at' => now(), 'updated_at' => now(),
            ]);

            $seg_casa_tanger = DB::table('segments')->insertGetId([
                'route_id'           => $routeCasaTanger,
                'departure_stop_id'  => $stop_casa_r3,
                'arrival_stop_id'    => $stop_tanger_r3,
                'distance_km'        => 345,
                'duration_minutes'   => 330,
                'created_at' => now(), 'updated_at' => now(),
            ]);

            // ===== 7. TARIFS (Fares) =====
            $faresData = [
                [$seg_casa_mrk,    'standard', 120.00],
                [$seg_rabat_casa,  'standard',  80.00],
                [$seg_casa_tanger, 'standard', 150.00],
            ];
            foreach ($faresData as [$segId, $type, $price]) {
                DB::table('fares')->insert([
                    'segment_id'     => $segId,
                    'bus_type'       => $type,
                    'price'          => $price,
                    'active'         => 1,
                    'effective_from' => '2024-01-01',
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]);
            }

            // ===== 8. SCHEDULES (Horaires hebdomadaires) =====
            $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

            $schedules = [];
            foreach ($days as $day) {
                // Ligne Casa → Marrakech : 2 départs par jour
                $schedules[] = DB::table('schedules')->insertGetId([
                    'route_id'       => $routeCasaMrk,
                    'departure_time' => '08:00:00',
                    'arrival_time'   => '12:00:00',
                    'day_of_week'    => $day,
                    'active'         => 1,
                    'created_at'     => now(), 'updated_at' => now(),
                ]);
                $schedules[] = DB::table('schedules')->insertGetId([
                    'route_id'       => $routeCasaMrk,
                    'departure_time' => '15:00:00',
                    'arrival_time'   => '19:00:00',
                    'day_of_week'    => $day,
                    'active'         => 1,
                    'created_at'     => now(), 'updated_at' => now(),
                ]);

                // Ligne Rabat → Casa : 1 départ par jour
                $schedules[] = DB::table('schedules')->insertGetId([
                    'route_id'       => $routeRabatCasa,
                    'departure_time' => '09:00:00',
                    'arrival_time'   => '10:30:00',
                    'day_of_week'    => $day,
                    'active'         => 1,
                    'created_at'     => now(), 'updated_at' => now(),
                ]);

                // Ligne Casa → Tanger : 1 départ par jour
                $schedules[] = DB::table('schedules')->insertGetId([
                    'route_id'       => $routeCasaTanger,
                    'departure_time' => '07:00:00',
                    'arrival_time'   => '12:30:00',
                    'day_of_week'    => $day,
                    'active'         => 1,
                    'created_at'     => now(), 'updated_at' => now(),
                ]);
            }

            // ===== 9. TRIPS (Voyages planifiés pour les 14 prochains jours) =====
            $today = Carbon::today();

            for ($i = 0; $i < 14; $i++) {
                $date      = $today->copy()->addDays($i);
                $dayOfWeek = strtolower($date->format('l'));

                // Récupérer tous les schedules actifs de ce jour
                $daySchedules = DB::table('schedules')
                    ->where('day_of_week', $dayOfWeek)
                    ->where('active', 1)
                    ->get();

                foreach ($daySchedules as $schedule) {
                    // Choisir un bus selon la ligne
                    $busIndex = ($schedule->route_id - 1) % count($busIds);
                    $busId    = $busIds[$busIndex];

                    // Vérifier si le trip n'existe pas déjà
                    $exists = DB::table('trips')
                        ->where('schedule_id', $schedule->id)
                        ->where('departure_date', $date->format('Y-m-d'))
                        ->exists();

                    if (!$exists) {
                        DB::table('trips')->insert([
                            'schedule_id'    => $schedule->id,
                            'bus_id'         => $busId,
                            'departure_date' => $date->format('Y-m-d'),
                            'status'         => 'scheduled',
                            'created_at'     => now(),
                            'updated_at'     => now(),
                        ]);
                    }
                }
            }

            $this->command->info('✅ TripDataSeeder terminé avec succès!');
            $this->command->info('Villes créées: ' . count($villes));
            $this->command->info('Routes créées: 3 (Casa↔Mrk, Rabat↔Casa, Casa↔Tanger)');
            $this->command->info('Trips créés pour les 14 prochains jours');

        } catch (\Exception $e) {
            $this->command->error("ERREUR: " . $e->getMessage());
            throw $e;
        }
    }
}
