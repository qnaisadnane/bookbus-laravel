<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\TripController;
use App\Http\Controllers\Admin\BusController;

// ========== PUBLIC ROUTES ==========


// Homepage / Search page
Route::get('/', [SearchController::class, 'index'])->name('home');

// Search results
Route::get('/search', [SearchController::class, 'search'])->name('search.results');

// Booking routes - require authentication
Route::middleware(['auth'])->group(function () {
    Route::get('/booking/create', [BookingController::class, 'create'])->name('booking.create');
    Route::post('/booking/store', [BookingController::class, 'store'])->name('booking.store');
    Route::get('/booking/{booking}/confirmation', [BookingController::class, 'confirmation'])->name('booking.confirmation');
    Route::get('/booking/{booking}', [BookingController::class, 'show'])->name('booking.show');
    Route::post('/booking/{booking}/cancel', [BookingController::class, 'cancel'])->name('booking.cancel');
    Route::get('/booking/seat-map', [BookingController::class, 'getSeatMap'])->name('booking.seatmap');
});

// ========== ADMIN ROUTES ==========

Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
    
    // Trips management
    Route::get('/trips', [TripController::class, 'index'])->name('admin.trips.index');
    Route::get('/trips/{trip}', [TripController::class, 'show'])->name('admin.trips.show');
    Route::post('/trips/{trip}/assign-resources', [TripController::class, 'assignResources'])->name('admin.trips.assign');
    Route::post('/trips/{trip}/cancel', [TripController::class, 'cancel'])->name('admin.trips.cancel');
    
    // Buses management
    Route::get('/buses', [BusController::class, 'index'])->name('admin.buses.index');
    Route::get('/buses/{bus}', [BusController::class, 'show'])->name('admin.buses.show');
    Route::post('/buses/{bus}/status', [BusController::class, 'updateStatus'])->name('admin.buses.status');
    Route::get('/buses/statistics', [BusController::class, 'statistics'])->name('admin.buses.statistics');
    
});

Route::get('/debug-search', function() {
    $villeDepartName = 'Casablanca';
    $villeArriveeName = 'Marrakech';
    
    $villeDepart = DB::table('ville')->where('name', $villeDepartName)->value('id');
    $villeArrivee = DB::table('ville')->where('name', $villeArriveeName)->value('id');
    
    echo "Ville Dep: $villeDepart, Arr: $villeArrivee <br>";
    
    $gareDepart = DB::table('gares')->where('id_ville', $villeDepart)->first();
    $gareArrivee = DB::table('gares')->where('id_ville', $villeArrivee)->first();
    
    echo "Gare Dep: " . ($gareDepart->id ?? 'Non') . ", Arr: " . ($gareArrivee->id ?? 'Non') . "<br>";
    
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
        
    echo "Routes bases: " . $routes->count() . "<br>";
    
    $prog = DB::table('programmes')->count();
    echo "Total Programmes: $prog <br>";
    
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
            ->select('programmes.jour_depart')
            ->get();
            
    echo "Trajets joined: " . $trajets->count() . "<br>";
    foreach($trajets as $t) {
        echo "Date: " . $t->jour_depart . "<br>";
    }
});

// Dashboard route (utilisé après login/register)
Route::get('/dashboard', function () {
    return redirect()->route('home');
})->middleware(['auth'])->name('dashboard');

// Authentication routes
require __DIR__.'/auth.php';