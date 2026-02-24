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
                        <h2 class="text-xl font-bold mb-4">Passagers ({{ $nombreVoyageurs }})</h2>
                        <div id="passengers-container" class="space-y-4">
                            @for($i = 0; $i < $nombreVoyageurs; $i++)
                            <div class="passenger-form space-y-3 pb-4 {{ $i < $nombreVoyageurs - 1 ? 'border-b' : '' }}">
                                <h3 class="font-semibold text-blue-600 mb-3">Passager {{ $i + 1 }}</h3>
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-sm font-medium mb-1">Prénom *</label>
                                        <input type="text" name="passengers[{{ $i }}][first_name]" required 
                                               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium mb-1">Nom *</label>
                                        <input type="text" name="passengers[{{ $i }}][last_name]" required 
                                               class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1">Email *</label>
                                    <input type="email" name="passengers[{{ $i }}][email]" required 
                                           class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1">Téléphone *</label>
                                    <input type="tel" name="passengers[{{ $i }}][phone]" required 
                                           class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                </div>
                            </div>
                            @endfor
                        </div>
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
                            <span class="text-gray-600">Tarif par passager</span>
                            <span class="font-bold">{{ number_format($fare->price, 2) }} MAD</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Nombre de passagers</span>
                            <span class="font-bold">{{ $nombreVoyageurs }}</span>
                        </div>
                        <div class="flex justify-between text-lg border-t pt-3">
                            <span class="font-bold">Total</span>
                            <span class="font-bold text-blue-600">{{ number_format($fare->price * $nombreVoyageurs, 2) }} MAD</span>
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
