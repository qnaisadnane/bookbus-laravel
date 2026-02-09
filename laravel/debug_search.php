<?php

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

// Mock Inputs
$villeDepartName = 'Casablanca';
$villeArriveeName = 'Marrakech';
$dateDepart = Carbon::today()->addDays(1)->format('Y-m-d'); // Tomorrow
$nombreVoyageurs = 1;

echo "Debug Search: $villeDepartName -> $villeArriveeName on $dateDepart\n";

// 1. Get IDs
$villeDepart = DB::table('ville')->where('name', $villeDepartName)->value('id');
$villeArrivee = DB::table('ville')->where('name', $villeArriveeName)->value('id');

echo "Ville Depart ID: $villeDepart\n";
echo "Ville Arrivee ID: $villeArrivee\n";

if (!$villeDepart || !$villeArrivee) {
    die("Cities not found.\n");
}

// 2. Get Gares
$gareDepart = DB::table('gares')->where('id_ville', $villeDepart)->first();
$gareArrivee = DB::table('gares')->where('id_ville', $villeArrivee)->first();

echo "Gare Depart: " . ($gareDepart->id ?? 'None') . "\n";
echo "Gare Arrivee: " . ($gareArrivee->id ?? 'None') . "\n";

if (!$gareDepart || !$gareArrivee) {
    die("Stations not found.\n");
}

// 3. Test Raw Query steps
// Find routes passing through both gares
$routes = DB::table('route')
    ->join('etapes as etape_depart', function($join) use ($gareDepart) {
        $join->on('route.id', '=', 'etape_depart.route_id')
             ->where('etape_depart.gare_id', '=', $gareDepart->id);
    })
    ->join('etapes as etape_arrivee', function($join) use ($gareArrivee) {
        $join->on('route.id', '=', 'etape_arrivee.route_id')
             ->where('etape_arrivee.gare_id', '=', $gareArrivee->id);
    })
    ->select('route.id', 'route.nom', 'etape_depart.ordre as ordre_dep', 'etape_arrivee.ordre as ordre_arr')
    ->get();

echo "Matching Routes count: " . $routes->count() . "\n";
foreach($routes as $r) {
    echo " - Route: {$r->id} ({$r->nom}), Ordre Dep: {$r->ordre_dep}, Ordre Arr: {$r->ordre_arr}\n";
}

// 4. Test Full Query with Programmes
$trajets = DB::table('route')
    ->join('etapes as etape_depart', function($join) use ($gareDepart) {
        $join->on('route.id', '=', 'etape_depart.route_id')
             ->where('etape_depart.gare_id', '=', $gareDepart->id);
    })
    ->join('etapes as etape_arrivee', function($join) use ($gareArrivee) {
        $join->on('route.id', '=', 'etape_arrivee.route_id')
             ->where('etape_arrivee.gare_id', '=', $gareArrivee->id);
    })
    ->join('programmes', 'route.id', '=', 'programmes.id_route')
    ->join('segments', 'programmes.segment_id', '=', 'segments.id')
    ->join('bus', 'segments.id_bus', '=', 'bus.id')
    ->where('etape_depart.ordre', '<', 'etape_arrivee.ordre')
    // ->where('programmes.jour_depart', $dateDepart) // Commented out to see if date is the issue
    ->select(
        'route.id as route_id',
        'programmes.jour_depart',
        'programmes.heure_depart'
    )
    ->get();

echo "Trajets found (ignoring date): " . $trajets->count() . "\n";
foreach($trajets->take(5) as $t) {
    echo " - Trajet on {$t->jour_depart} at {$t->heure_depart}\n";
}
