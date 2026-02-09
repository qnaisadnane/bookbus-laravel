<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class VoyageSeeder extends Seeder
{
    public function run()
    {
        try {
            // 1. Clean up relevant tables
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            DB::table('programmes')->truncate();
            DB::table('segments')->truncate();
            DB::table('etapes')->truncate();
            DB::table('route')->truncate();
            DB::table('bus')->truncate();
            DB::table('gares')->truncate();
            DB::table('ville')->truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            // 2. Insert Cities
            $cities = [
                ['name' => 'Casablanca'],
                ['name' => 'Marrakech'],
                ['name' => 'Rabat']
            ];

            foreach ($cities as $city) {
                DB::table('ville')->insert($city);
            }

            // Get City IDs
            $casaId = DB::table('ville')->where('name', 'Casablanca')->value('id');
            $marrakechId = DB::table('ville')->where('name', 'Marrakech')->value('id');
            $rabatId = DB::table('ville')->where('name', 'Rabat')->value('id');

            // 3. Insert Stations (Gares)
            $stations = [
                ['nom' => 'Gare Routière Ouled Ziane', 'adresse' => 'Route Ouled Ziane', 'id_ville' => $casaId],
                ['nom' => 'Gare Routière Bab Doukkala', 'adresse' => 'Bab Doukkala', 'id_ville' => $marrakechId],
                ['nom' => 'Gare Routière Kamra', 'adresse' => 'Avenue Hassan II', 'id_ville' => $rabatId],
            ];
            
            foreach ($stations as $station) {
                DB::table('gares')->insert($station);
            }
            
            $gareCasa = DB::table('gares')->where('id_ville', $casaId)->value('id');
            $gareMarrakech = DB::table('gares')->where('id_ville', $marrakechId)->value('id');

            // 4. Insert Buses
            $buses = [];
            for ($i = 1; $i <= 10; $i++) {
                 $buses[] = [
                    'matricule' => rand(1000, 9999) . '-A-' . rand(1, 80),
                    'capacite' => 50,
                 ];
            }
            DB::table('bus')->insert($buses);
            $busIds = DB::table('bus')->pluck('id')->toArray();

            // 5. Create Routes (Lignes)
            // Route 1: Casablanca -> Marrakech
            $routeId = DB::table('route')->insertGetId([
                'nom' => 'Ligne 101',
                'description' => 'Express Casablanca - Marrakech'
            ]);

            // 6. Create Segment FIRST (needed for Etapes)
            $segmentId = DB::table('segments')->insertGetId([
                'tarif' => 120.00,
                'distance_km' => 240,
                'id_bus' => $busIds[0],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 7. Create Etapes (Stops) for this route
            // Depart Casablanca (Ordre 1)
            DB::table('etapes')->insert([
                'route_id' => $routeId,
                'gare_id' => $gareCasa,
                'segment_id' => $segmentId, // Added missing segment_id
                'ordre' => 1,
                'heure_passage' => '08:00:00',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Arrivee Marrakech (Ordre 2)
            DB::table('etapes')->insert([
                'route_id' => $routeId,
                'gare_id' => $gareMarrakech,
                'segment_id' => $segmentId, // Added missing segment_id
                'ordre' => 2,
                'heure_passage' => '12:00:00',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 8. Create Programmes (Schedules) for the next 7 days
            $today = Carbon::today();
            
            for ($i = 0; $i < 7; $i++) {
                $date = $today->copy()->addDays($i);
                
                // Morning Bus
                DB::table('programmes')->insert([
                    'id_route' => $routeId,
                    'segment_id' => $segmentId,
                    'jour_depart' => $date->format('Y-m-d'),
                    'heure_depart' => '08:00:00',
                    'heure_arrivee' => '12:00:00',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Afternoon Bus
                DB::table('programmes')->insert([
                    'id_route' => $routeId,
                    'segment_id' => $segmentId, 
                    'jour_depart' => $date->format('Y-m-d'),
                    'heure_depart' => '15:00:00',
                    'heure_arrivee' => '19:00:00',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        } catch (\Exception $e) {
            $this->command->error("ERROR SEEDING: " . $e->getMessage());
            throw $e;
        }
    }
}
