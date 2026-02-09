@extends('layouts.app')

@section('title', 'Tableau de bord Admin - SATAS')

@section('content')
<div class="min-h-screen bg-gray-100">
    <!-- Header -->
    <div class="bg-white shadow">
        <div class="max-w-7xl mx-auto px-4 py-6">
            <h1 class="text-3xl font-bold text-blue-600">Tableau de bord Admin SATAS</h1>
            <p class="text-gray-600">Gestion des trajets, bus et chauffeurs</p>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 py-8">
        <!-- Statistics -->
        <div class="grid grid-cols-4 gap-4 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <p class="text-gray-600 text-sm">Total Bus</p>
                <p class="text-3xl font-bold text-blue-600">{{ $stats['total_buses'] }}</p>
                <p class="text-xs text-green-600 mt-2">
                    {{ $stats['buses_in_service'] }} en service
                </p>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <p class="text-gray-600 text-sm">Chauffeurs</p>
                <p class="text-3xl font-bold text-green-600">{{ $stats['active_drivers'] }}/{{ $stats['total_drivers'] }}</p>
                <p class="text-xs text-gray-600 mt-2">actifs</p>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <p class="text-gray-600 text-sm">Trajets aujourd'hui</p>
                <p class="text-3xl font-bold text-orange-600">{{ $stats['total_trips_today'] }}</p>
                <p class="text-xs text-gray-600 mt-2">programmés</p>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <p class="text-gray-600 text-sm">Trajets complétés</p>
                <p class="text-3xl font-bold text-purple-600">{{ $stats['completed_trips'] }}</p>
                <p class="text-xs text-red-600 mt-2">
                    {{ $stats['cancelled_trips'] }} annulés
                </p>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="bg-white rounded-lg shadow mb-8">
            <div class="flex border-b">
                <a href="#trips" class="flex-1 px-6 py-4 font-semibold border-b-2 border-blue-600 text-blue-600">
                    Trajets à venir
                </a>
                <a href="#buses" class="flex-1 px-6 py-4 font-semibold text-gray-600 hover:text-gray-900">
                    Parc bus
                </a>
                <a href="#resources" class="flex-1 px-6 py-4 font-semibold text-gray-600 hover:text-gray-900">
                    Affectations
                </a>
            </div>
        </div>

        <!-- Upcoming Trips -->
        <div id="trips" class="bg-white rounded-lg shadow p-6 mb-8">
            <h2 class="text-2xl font-bold mb-4">Trajets à venir</h2>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left">Route</th>
                            <th class="px-4 py-2 text-left">Date</th>
                            <th class="px-4 py-2 text-left">Horaire</th>
                            <th class="px-4 py-2 text-left">Bus</th>
                            <th class="px-4 py-2 text-left">Chauffeur</th>
                            <th class="px-4 py-2 text-left">Statut</th>
                            <th class="px-4 py-2 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse ($upcomingTrips as $trip)
                            <tr>
                                <td class="px-4 py-3 font-semibold">{{ $trip->schedule->route->nom }}</td>
                                <td class="px-4 py-3">{{ $trip->departure_date->format('d/m/Y') }}</td>
                                <td class="px-4 py-3">{{ $trip->schedule->departure_time }}</td>
                                <td class="px-4 py-3">
                                    @if ($trip->bus)
                                        <span class="text-sm">{{ $trip->bus->registration_number }}</span>
                                    @else
                                        <span class="text-red-600 text-sm">Non assigné</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @if ($trip->assignments->first())
                                        <span class="text-sm">{{ $trip->assignments->first()->driver->full_name }}</span>
                                    @else
                                        <span class="text-red-600 text-sm">Non assigné</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold
                                        {{ $trip->status === 'scheduled' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700' }}">
                                        {{ ucfirst($trip->status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <a href="{{ route('admin.trips.show', $trip->id) }}" 
                                       class="text-blue-600 hover:underline text-sm font-semibold">
                                        Gérer
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-6 text-center text-gray-500">
                                    Aucun trajet prévu
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Bus Status -->
        <div id="buses" class="bg-white rounded-lg shadow p-6">
            <h2 class="text-2xl font-bold mb-4">État du parc bus</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-green-50 p-4 rounded border border-green-200">
                    <p class="text-green-700 font-semibold">En service</p>
                    <p class="text-2xl font-bold text-green-600">{{ $stats['buses_in_service'] }}</p>
                </div>
                <div class="bg-orange-50 p-4 rounded border border-orange-200">
                    <p class="text-orange-700 font-semibold">Maintenance</p>
                    <p class="text-2xl font-bold text-orange-600">{{ $stats['buses_in_maintenance'] }}</p>
                </div>
                <div class="bg-red-50 p-4 rounded border border-red-200">
                    <p class="text-red-700 font-semibold">Hors service</p>
                    <p class="text-2xl font-bold text-red-600">{{ $stats['total_buses'] - $stats['buses_in_service'] - $stats['buses_in_maintenance'] }}</p>
                </div>
                <div class="bg-blue-50 p-4 rounded border border-blue-200">
                    <p class="text-blue-700 font-semibold">Capacité totale</p>
                    <p class="text-2xl font-bold text-blue-600">{{ array_sum($busStats->pluck('id')->toArray()) }}</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left">Immatriculation</th>
                            <th class="px-4 py-2 text-left">Type</th>
                            <th class="px-4 py-2 text-left">Statut</th>
                            <th class="px-4 py-2 text-left">Trajets actifs</th>
                            <th class="px-4 py-2 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse ($busStats as $bus)
                            <tr>
                                <td class="px-4 py-3 font-semibold">{{ $bus['registration'] }}</td>
                                <td class="px-4 py-3 text-sm">{{ ucfirst($bus['type']) }}</td>
                                <td class="px-4 py-3">
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold
                                        {{ $bus['status'] === 'in_service' ? 'bg-green-100 text-green-700' : 'bg-orange-100 text-orange-700' }}">
                                        {{ ucfirst($bus['status']) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">{{ $bus['active_trips'] }}</td>
                                <td class="px-4 py-3">
                                    <a href="{{ route('admin.buses.show', $bus['id']) }}" 
                                       class="text-blue-600 hover:underline text-sm font-semibold">
                                        Voir
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-6 text-center text-gray-500">
                                    Aucun bus
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
