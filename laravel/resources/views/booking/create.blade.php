@extends('layouts.app')

@section('title', 'Réservation - SATAS')

@section('content')
<div class="min-h-screen bg-gray-100">
    <!-- Header -->
    <div class="bg-white shadow">
        <div class="max-w-4xl mx-auto px-4 py-6">
            <h1 class="text-3xl font-bold text-blue-600">Finaliser votre réservation</h1>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 py-8">
        <div class="grid grid-cols-3 gap-6">
            
            <!-- Booking Form -->
            <div class="col-span-2">
                <form method="POST" action="{{ route('booking.store') }}" class="space-y-6">
                    @csrf

                    <input type="hidden" name="trip_id" value="{{ $trip->id }}">
                    <input type="hidden" name="segment_id" value="{{ $segment->id }}">

                    <!-- Trip Details -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-xl font-bold mb-4">Détails du trajet</h2>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-gray-600 text-sm">Départ</p>
                                <p class="text-lg font-bold">{{ $segment->departureStop->station->name }}</p>
                                <p class="text-blue-600">{{ $trip->schedule->departure_time }}</p>
                            </div>
                            <div>
                                <p class="text-gray-600 text-sm">Arrivée</p>
                                <p class="text-lg font-bold">{{ $segment->arrivalStop->station->name }}</p>
                                <p class="text-blue-600">{{ $trip->schedule->arrival_time }}</p>
                            </div>
                            <div>
                                <p class="text-gray-600 text-sm">Bus</p>
                                <p class="text-lg font-bold">{{ ucfirst($trip->bus->type) }}</p>
                                <p class="text-gray-600">{{ $trip->bus->registration_number }}</p>
                            </div>
                            <div>
                                <p class="text-gray-600 text-sm">Chauffeur</p>
                                @if ($trip->driver)
                                    <p class="text-lg font-bold">{{ $trip->driver->full_name }}</p>
                                @else
                                    <p class="text-gray-500">À assigner</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Passengers -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-xl font-bold mb-4">Passagers</h2>
                        <div id="passengers-container" class="space-y-4">
                            <div class="passenger-form space-y-3 pb-4 border-b">
                                <div>
                                    <label class="block text-sm font-medium mb-1">Prénom *</label>
                                    <input type="text" name="passengers[0][first_name]" required 
                                           class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1">Nom *</label>
                                    <input type="text" name="passengers[0][last_name]" required 
                                           class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1">Email *</label>
                                    <input type="email" name="passengers[0][email]" required 
                                           class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1">Téléphone *</label>
                                    <input type="tel" name="passengers[0][phone]" required 
                                           class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1">Document d'identité</label>
                                    <input type="text" name="passengers[0][id_document]" 
                                           class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                           placeholder="N° passeport, CIN...">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Options -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-xl font-bold mb-4">Options supplémentaires</h2>

                        <!-- Insurance -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-2">Assurance annulation</label>
                            <div class="space-y-2">
                                <label class="flex items-center">
                                    <input type="radio" name="insurance" value="none" checked class="mr-2">
                                    <span>Aucune</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" name="insurance" value="partial" class="mr-2">
                                    <span>Partielle (80% remboursement) - 5% du prix</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" name="insurance" value="full" class="mr-2">
                                    <span>Complète (100% remboursement) - 8% du prix</span>
                                </label>
                            </div>
                        </div>

                        <!-- Snack Box -->
                        <div class="border-t pt-4">
                            <label class="flex items-center">
                                <input type="checkbox" name="snackbox" value="1" class="mr-2">
                                <span class="font-medium">Snack-box SATAS</span>
                                <span class="text-gray-600 ml-auto">+15 MAD</span>
                            </label>
                        </div>
                    </div>

                    <!-- Promo Code -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-xl font-bold mb-4">Code promotionnel</h2>
                        <input type="text" name="promo_code" placeholder="Entrez votre code promo" 
                               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <p class="text-sm text-gray-500 mt-2">Codes valides: SATAS10, SATAS15, SATAS20, LOYALTY5</p>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="w-full bg-blue-600 text-white font-bold py-3 rounded-lg hover:bg-blue-700 transition">
                        Confirmer la réservation
                    </button>
                </form>
            </div>

            <!-- Summary Sidebar -->
            <div class="col-span-1">
                <div class="bg-white rounded-lg shadow p-6 sticky top-4">
                    <h3 class="text-xl font-bold mb-4">Résumé</h3>
                    
                    <div class="space-y-3 mb-6 pb-6 border-b">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Tarif segment</span>
                            <span class="font-bold">{{ number_format($fare->price, 2) }} MAD</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Places disponibles</span>
                            <span class="font-bold text-green-600">{{ $availableSeats }}</span>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <p class="text-sm text-gray-600">Les options seront ajoutées au total lors de la validation</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
