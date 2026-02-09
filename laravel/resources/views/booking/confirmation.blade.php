@extends('layouts.app')

@section('title', 'Confirmation de réservation - SATAS')

@section('content')
<div class="min-h-screen bg-gray-100">
    <div class="max-w-2xl mx-auto px-4 py-12">
        <!-- Success Card -->
        <div class="bg-white rounded-lg shadow-lg p-8 text-center mb-8">
            <div class="text-6xl text-green-500 mb-4">✓</div>
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Réservation confirmée!</h1>
            <p class="text-gray-600">Un email de confirmation a été envoyé à votre adresse</p>
            <p class="text-lg font-bold text-blue-600 mt-4">Numéro de réservation: #{{ $booking->id }}</p>
        </div>

        <!-- Booking Details -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-xl font-bold mb-4">Détails de la réservation</h2>

            <!-- Trip Info -->
            <div class="grid grid-cols-2 gap-6 mb-6 pb-6 border-b">
                <div>
                    <p class="text-sm text-gray-600">Départ</p>
                    <p class="text-lg font-bold">{{ $booking->segment->departureStop->station->name }}</p>
                    <p class="text-blue-600">{{ $booking->trip->schedule->departure_time }}</p>
                    <p class="text-gray-600 text-sm">{{ $booking->trip->departure_date->format('d/m/Y') }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Arrivée</p>
                    <p class="text-lg font-bold">{{ $booking->segment->arrivalStop->station->name }}</p>
                    <p class="text-blue-600">{{ $booking->trip->schedule->arrival_time }}</p>
                </div>
            </div>

            <!-- Bus & Driver Info -->
            <div class="grid grid-cols-2 gap-6 mb-6 pb-6 border-b">
                <div>
                    <p class="text-sm text-gray-600">Bus</p>
                    <p class="text-lg font-bold">{{ ucfirst($booking->trip->bus->type) }}</p>
                    <p class="text-gray-600">{{ $booking->trip->bus->registration_number }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600">Chauffeur</p>
                    @if ($booking->trip->assignments->first())
                        <p class="text-lg font-bold">{{ $booking->trip->assignments->first()->driver->full_name }}</p>
                        <p class="text-gray-600">{{ $booking->trip->assignments->first()->driver->phone }}</p>
                    @else
                        <p class="text-gray-500">À assigner</p>
                    @endif
                </div>
            </div>

            <!-- Passengers -->
            <div class="mb-6">
                <h3 class="font-bold mb-3">Passagers</h3>
                <div class="space-y-2">
                    @foreach ($booking->passengers as $passenger)
                        <div class="flex items-center justify-between bg-gray-50 p-3 rounded">
                            <span>{{ $passenger->full_name }}</span>
                            <span class="text-sm text-gray-600">Siège: {{ $passenger->seat_number ?? 'À assigner' }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Price Summary -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-xl font-bold mb-4">Résumé du paiement</h2>
            
            <div class="space-y-2 mb-4">
                <div class="flex justify-between">
                    <span>Tarif segment</span>
                    <span>{{ number_format($booking->segment_price, 2) }} MAD</span>
                </div>
                @if ($booking->snackbox_price > 0)
                    <div class="flex justify-between">
                        <span>Snack-box SATAS</span>
                        <span>{{ number_format($booking->snackbox_price, 2) }} MAD</span>
                    </div>
                @endif
                @if ($booking->insurance_price > 0)
                    <div class="flex justify-between">
                        <span>Assurance {{ $booking->insurance }}</span>
                        <span>{{ number_format($booking->insurance_price, 2) }} MAD</span>
                    </div>
                @endif
                @if ($booking->discount_amount > 0)
                    <div class="flex justify-between text-green-600">
                        <span>Remise ({{ $booking->promo_code }})</span>
                        <span>-{{ number_format($booking->discount_amount, 2) }} MAD</span>
                    </div>
                @endif
            </div>

            <div class="border-t pt-4">
                <div class="flex justify-between text-xl font-bold">
                    <span>Total à payer</span>
                    <span class="text-blue-600">{{ number_format($booking->total_price, 2) }} MAD</span>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex gap-4">
            <a href="{{ route('home') }}" class="flex-1 bg-gray-200 text-gray-800 font-bold py-3 rounded-lg text-center hover:bg-gray-300 transition">
                Accueil
            </a>
            <a href="{{ route('booking.show', $booking->id) }}" class="flex-1 bg-blue-600 text-white font-bold py-3 rounded-lg text-center hover:bg-blue-700 transition">
                Voir ma réservation
            </a>
        </div>

        <!-- Important Info -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mt-8">
            <h3 class="font-bold text-blue-900 mb-2">ℹ️ Informations importantes</h3>
            <ul class="text-sm text-blue-900 space-y-1">
                <li>• Présentez-vous 30 minutes avant le départ</li>
                <li>• Annulation gratuite jusqu'à 24h avant le départ</li>
                <li>• Remboursement de 50% si annulation entre 0-24h</li>
                <li>• Un email de confirmation a été envoyé</li>
            </ul>
        </div>
    </div>
</div>
@endsection
