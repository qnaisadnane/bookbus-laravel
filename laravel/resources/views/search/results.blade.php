@extends('layouts.app')

@section('title', 'Résultats - BookBus Maroc')

@section('content')
<div class="min-h-screen bg-slate-50 font-sans text-slate-800">
    
    <!-- Header Summary -->
    <div class="bg-white border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 lg:px-8 py-4">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <div class="flex items-center gap-6 text-sm">
                    <div class="flex items-center gap-2">
                        <i class="bi bi-geo-alt-fill text-blue-600"></i>
                        <span class="font-bold text-slate-900">{{ $nomVilleDepart }}</span>
                    </div>
                    <i class="bi bi-arrow-right text-slate-400"></i>
                    <div class="flex items-center gap-2">
                        <i class="bi bi-pin-map-fill text-blue-600"></i>
                        <span class="font-bold text-slate-900">{{ $nomVilleArrivee }}</span>
                    </div>
                    <div class="hidden md:flex h-4 w-px bg-slate-300 mx-2"></div>
                    <div class="hidden md:flex items-center gap-2 text-slate-600">
                        <i class="bi bi-calendar-event"></i>
                        {{ \Carbon\Carbon::parse($dateDepart)->locale('fr')->isoFormat('DD MMM YYYY') }}
                    </div>
                    <div class="hidden md:flex items-center gap-2 text-slate-600">
                        <i class="bi bi-people"></i>
                        {{ $nombreVoyageurs }}
                    </div>
                </div>

                <a href="{{ route('home') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium hover:underline">
                    Modifier la recherche
                </a>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            
            <!-- Sidebar Filters -->
            @if($trajets->isNotEmpty())
            <div class="lg:col-span-1 space-y-6">
                <!-- Search Count -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
                    <div class="text-3xl font-bold text-slate-900">{{ $trajets->count() }}</div>
                    <div class="text-sm text-slate-500 font-medium">Trajets disponibles</div>
                </div>

                <!-- Filters -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 space-y-6">
                    <h3 class="font-bold text-slate-800 flex items-center gap-2 pb-4 border-b border-slate-100">
                        <i class="bi bi-sliders"></i> Filtres
                    </h3>
                    
                    <!-- Class Filter -->
                    <div class="space-y-3">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wide">Classe</label>
                        <div class="space-y-2 classe-buttons">
                            <button class="classe-btn active w-full py-2 px-3 rounded-lg border border-blue-600 bg-blue-50 text-blue-700 text-sm font-medium flex justify-between items-center group transition-all" data-classe="standard">
                                <span>Standard</span>
                                <i class="bi bi-check-circle-fill"></i>
                            </button>
                            <button class="classe-btn w-full py-2 px-3 rounded-lg border border-slate-200 hover:border-slate-300 text-slate-600 text-sm transition-all flex justify-between items-center group" data-classe="confort">
                                <span>Confort</span>
                                <i class="bi bi-circle text-slate-300 group-hover:text-slate-400"></i>
                            </button>
                            <button class="classe-btn w-full py-2 px-3 rounded-lg border border-slate-200 hover:border-slate-300 text-slate-600 text-sm transition-all flex justify-between items-center group" data-classe="premium">
                                <span>Premium</span>
                                <i class="bi bi-circle text-slate-300 group-hover:text-slate-400"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Time Filter -->
                    <div class="space-y-3">
                        <label for="filtre-heure" class="text-xs font-bold text-slate-500 uppercase tracking-wide">Heure de départ</label>
                        <select id="filtre-heure" class="w-full bg-slate-50 border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-700 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Toutes les heures</option>
                            <option value="matin">Matin (05:00 - 12:00)</option>
                            <option value="apres-midi">Après-midi (12:00 - 18:00)</option>
                            <option value="soir">Soir (18:00 - 00:00)</option>
                        </select>
                    </div>

                    <!-- Price Filter -->
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <label for="prix-max" class="text-xs font-bold text-slate-500 uppercase tracking-wide">Prix max</label>
                            <span id="prix-value" class="text-blue-600 font-bold text-sm">500 DH</span>
                        </div>
                        <input type="range" id="prix-max" min="50" max="500" value="500" step="10" 
                               class="w-full h-2 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-blue-600">
                    </div>

                    <button id="reset-filtres" class="w-full py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-lg text-sm font-medium transition-colors">
                        Tout réinitialiser
                    </button>
                </div>
            </div>
            @endif

            <!-- Results List -->
            <div class="lg:col-span-3 space-y-4 trajets-container">
                @if($trajets->isNotEmpty())
                    @foreach($trajets as $trajet)
                        <div class="trajet-card bg-white border border-slate-200 rounded-xl hover:shadow-md transition-shadow duration-200 overflow-hidden"
                             data-prix-base="{{ $trajet->fare }}"
                             data-heure-depart="{{ \Carbon\Carbon::parse($trajet->departure_time)->format('H') }}"
                             data-duree="{{ $trajet->duree_minutes }}">
                            
                            <div class="p-6 grid grid-cols-1 md:grid-cols-12 gap-6 items-center">
                                
                                <!-- Carrier Info -->
                                <div class="md:col-span-3">
                                    <div class="flex items-center gap-2 mb-1">
                                        <i class="bi bi-bus-front text-blue-600"></i>
                                        <span class="font-bold text-slate-800">{{ $trajet->route_nom }}</span>
                                    </div>
                                    <span class="inline-block px-2 py-0.5 bg-blue-50 text-blue-700 text-xs font-semibold rounded">
                                        Standard
                                    </span>
                                </div>

                                <!-- Times & Duration -->
                                <div class="md:col-span-6 flex items-center justify-between px-4 md:px-8 border-l-0 md:border-l border-r-0 md:border-r border-slate-100">
                                    <div class="text-center">
                                        <div class="text-xl font-bold text-slate-900">
                                            {{ \Carbon\Carbon::parse($trajet->departure_time)->format('H:i') }}
                                        </div>
                                        <div class="text-xs text-slate-500">Départ</div>
                                    </div>

                                    <div class="flex flex-col items-center flex-1 px-4">
                                        <div class="text-xs text-slate-400 mb-1">
                                            {{ floor($trajet->duree_minutes / 60) }}h {{ $trajet->duree_minutes % 60 }}min
                                        </div>
                                        <div class="w-full h-px bg-slate-300 relative">
                                            <div class="absolute left-0 top-1/2 -translate-y-1/2 w-1.5 h-1.5 rounded-full bg-slate-300"></div>
                                            <div class="absolute right-0 top-1/2 -translate-y-1/2 w-1.5 h-1.5 rounded-full bg-slate-300"></div>
                                        </div>
                                        <div class="text-xs text-slate-400 mt-1">Direct</div>
                                    </div>

                                    <div class="text-center">
                                        <div class="text-xl font-bold text-slate-900">
                                            {{ \Carbon\Carbon::parse($trajet->arrival_time)->format('H:i') }}
                                        </div>
                                        <div class="text-xs text-slate-500">Arrivée</div>
                                    </div>
                                </div>

                                <!-- Price & Button -->
                                <div class="md:col-span-3 text-right">
                                    <div class="text-2xl font-bold text-slate-900 mb-1">
                                        {{ number_format($trajet->prix_final * $nombreVoyageurs, 0) }} DH
                                    </div>
                                    
                                    @if($trajet->peut_reserver)
                                        <a href="{{ route('booking.create', [
                                            'trip_id' => $trajet->trip_id,
                                            'segment_id' => $trajet->segment_id,
                                            'nombre_voyageurs' => $nombreVoyageurs
                                        ]) }}" 
                                           class="block w-full py-2.5 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-700 transition-colors shadow-sm text-center">
                                            Sélectionner
                                        </a>
                                        <div class="text-xs text-green-600 mt-2 font-medium">
                                            {{ $trajet->places_disponibles }} places restantes
                                        </div>
                                    @else
                                        <button disabled class="w-full py-2.5 bg-slate-100 text-slate-400 font-bold rounded-lg cursor-not-allowed">
                                            Complet
                                        </button>
                                    @endif
                                </div>
                            </div>

                            <!-- Footer Info -->
                            <div class="px-6 py-3 bg-slate-50 border-t border-slate-100 flex flex-wrap gap-4 text-xs text-slate-500">
                                <span class="flex items-center gap-1"><i class="bi bi-wifi"></i> Wifi</span>
                                <span class="flex items-center gap-1"><i class="bi bi-battery-charging"></i> Prises</span>
                                <span class="flex items-center gap-1 ml-auto">Bus: {{ $trajet->matricule }}</span>
                            </div>
                        </div>
                    @endforeach
                @else
                    <!-- Empty State -->
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-12 text-center">
                        <div class="w-20 h-20 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-6">
                            <i class="bi bi-search text-3xl text-slate-400"></i>
                        </div>
                        <h2 class="text-xl font-bold text-slate-800 mb-2">Aucun trajet trouvé</h2>
                        <p class="text-slate-500 max-w-md mx-auto mb-8">
                            Essayez de modifier votre date de départ ou les villes sélectionnées.
                        </p>
                        
                        @if(!empty($datesAlternatives))
                            <div class="text-left max-w-lg mx-auto">
                                <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wide mb-3 pl-1">Prochains départs disponibles :</h4>
                                <div class="grid gap-2">
                                    @foreach($datesAlternatives as $suggestion)
                                        <a href="{{ route('search.results', [
                                             'ville_depart' => request('ville_depart'),
                                             'ville_arrivee' => request('ville_arrivee'),
                                             'date_depart' => $suggestion['date'],
                                             'nombre_voyageurs' => $nombreVoyageurs
                                         ]) }}" 
                                           class="p-3 bg-white border border-slate-200 rounded-lg hover:border-blue-500 hover:ring-1 hover:ring-blue-500 transition-all flex justify-between items-center group">
                                            <span class="font-medium text-slate-700 group-hover:text-blue-700">{{ $suggestion['date_formatee'] }}</span>
                                            <span class="text-xs bg-green-100 text-green-700 px-2 py-1 rounded">
                                                {{ $suggestion['nombre_trajets'] }} bus
                                            </span>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <a href="{{ route('home') }}" class="inline-flex items-center px-6 py-2.5 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-700">
                                Touvelle recherche
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script>
        document.getElementById('prix-max')?.addEventListener('input', function(e) {
            document.getElementById('prix-value').textContent = e.target.value + ' DH';
        });
    </script>
@endpush
