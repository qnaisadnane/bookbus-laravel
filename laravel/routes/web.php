<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SearchController;

// Page d'accueil = Formulaire de recherche BookBus
Route::get('/', [SearchController::class, 'index'])->name('search.index');

// Page de résultats
Route::get('/search', [SearchController::class, 'search'])->name('search.results');

// Détails d'un trajet (optionnel)
Route::get('/trajet/{id}', [SearchController::class, 'details'])->name('trajet.details');