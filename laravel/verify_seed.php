<?php
use App\Models\Programme;
use App\Models\Route;
use App\Models\Ville;
use Carbon\Carbon;

$casa = Ville::where('name', 'Casablanca')->first();
$marrakech = Ville::where('name', 'Marrakech')->first();

echo "Casablanca ID: " . ($casa->id ?? 'Not Found') . "\n";
echo "Marrakech ID: " . ($marrakech->id ?? 'Not Found') . "\n";

$routes = Route::all();
echo "Routes count: " . $routes->count() . "\n";

$programmes = Programme::where('jour_depart', '>=', Carbon::today()->format('Y-m-d'))->get();
echo "Programmes future count: " . $programmes->count() . "\n";

foreach($programmes->take(3) as $prog) {
    echo "Prog: " . $prog->jour_depart . " " . $prog->heure_depart . "\n";
}
