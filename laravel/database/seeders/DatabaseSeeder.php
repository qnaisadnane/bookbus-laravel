<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use  Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $casa = DB::table('ville')->insertGetId(['name' => 'Casablanca']);
        $kech = DB::table('ville')->insertGetId(['name' => 'Marrakech']);
        $rabat = DB::table('ville')->insertGetId(['name' => 'Rabat']);

        $gareCasa = DB::table('gares')->insertGetId([
            'nom'=>'SATAS CASA MAARIF',
            'adresse'=>'Boulvard MOHAMED V ,quartier maarif ',
            'id_ville'=>$casa
        ]);

        $gareMarrakech = DB::table('gares')->insertGetId([
        'nom' => 'SATAS Marrakech',
        'adresse' => 'Centre',
        'id_ville' => $kech
        ]);

        $gareRabat = DB::table('gares')->insertGetId([
        'nom' => 'SATAS Rabat',
        'adresse' => 'Centre',
        'id_ville' => $rabat
        ]);

        $route1 = DB::table('route')->insertGetId([
        'nom'=>'casa -> Marrakech (Direct)'
        ]);

        $route2 = DB::table('route')->insertGetId([
        'nom'=>'Casa -> Rabat -> Marrakech '
        ]);

        DB::table('etapes')->insert([
        ['route_id'=>$route1 , 'gare_id'=>$gareCasa, 'segment_id'=>1 , ordre=>1 , 'heure_passage'=>'08:00'],
        ['route_id'=>$route1 , 'gare_id'=>$gareMarrakech, 'segment_id'=>1 , ordre=>2 , 'heure_passage'=>'11:00'],
        ['route_id'=>$route2 , 'gare_id'=>$gareCasa, 'segment_id'=>2 , ordre=>1 , 'heure_passage'=>'09:00'],
        ['route_id'=>$route2 , 'gare_id'=>$gareRabat, 'segment_id'=>3 , ordre=>2 , 'heure_passage'=>'10:30'],
        ['route_id'=>$route2 , 'gare_id'=>$garecasa, 'segment_id'=>3 , ordre=>3 , 'heure_passage'=>'12:30'],
        ]);

        DB::table('programmes')->insert([
        ['id_route'=>$route1 , 'id_segment'=>1, 'jour_depart'=>now()->addDay(),'heure_depart'=>'08:00','heure_arrivee'=>'11:00'],
        ['id_route'=>$route2 , 'id_segment'=>2, 'jour_depart'=>now()->addDay(),'heure_depart'=>'09:00','heure_arrivee'=>'12:30'],
        ]);


    }
}
