@extends('layouts.app')

@section('title', 'Réservez votre billet - BookBus Maroc')

@section('content')
<div class="min-h-screen bg-slate-50 flex items-center justify-center p-4">
    <div class="w-full max-w-6xl">
        
        <!-- Navbar Placeholder (Visual only) -->
        <div class="absolute top-0 left-0 w-full p-6 flex justify-between items-center bg-white/50 backdrop-blur-sm border-b border-slate-200">
            <div class="text-xl font-bold text-blue-900 flex items-center gap-2">
                <i class="bi bi-bus-front-fill text-blue-600"></i> BookBus
            </div>
            <div class="text-sm text-slate-500">Service Client: <span class="font-semibold text-slate-700">+212 522 000 000</span></div>
        </div>

        <div class="grid md:grid-cols-2 gap-12 items-center mt-16 md:mt-0">
            <!-- Left Side: Text -->
            <div class="space-y-6 text-center md:text-left">
                <div class="inline-block px-4 py-1.5 bg-blue-100 text-blue-700 text-sm font-semibold rounded-full mb-2">
                    Nouveau : Lignes Express disponibles
                </div>
                <h1 class="text-4xl md:text-6xl font-extrabold text-slate-900 leading-tight">
                    Voyagez à travers le <span class="text-blue-600">Maroc</span> en toute sérénité.
                </h1>
                <p class="text-lg text-slate-600 max-w-lg mx-auto md:mx-0">
                    Réservez vos billets de bus en quelques clics avec SATAS. Confort, sécurité et ponctualité garantis.
                </p>
                <div class="flex flex-wrap gap-4 justify-center md:justify-start">
                    <div class="flex items-center gap-2 text-slate-700 bg-white px-4 py-2 rounded-lg border border-slate-200 shadow-sm">
                        <i class="bi bi-wifi text-blue-500"></i> Wifi Gratuit
                    </div>
                    <div class="flex items-center gap-2 text-slate-700 bg-white px-4 py-2 rounded-lg border border-slate-200 shadow-sm">
                        <i class="bi bi-雪 text-blue-500"></i> Climatisation
                    </div>
                </div>
            </div>

            <!-- Right Side: Search Form -->
            <div class="bg-white rounded-2xl shadow-xl border border-slate-200 p-8">
                <div class="mb-6">
                    <h2 class="text-2xl font-bold text-slate-800">Où allez-vous ?</h2>
                    <p class="text-slate-500">Comparez les prix et les horaires instantanément.</p>
                </div>

                @if ($errors->any())
                    <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="bi bi-exclamation-circle text-red-500"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-red-700">
                                    @foreach ($errors->all() as $error)
                                        {{ $error }}<br>
                                    @endforeach
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                <form action="{{ route('search.results') }}" method="GET" class="space-y-5">
                    
                    <!-- Cities Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 relative">
                        <!-- Swap Button Absolute -->
                        <button type="button" onclick="swapCities()" class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 w-10 h-10 bg-white border border-slate-200 rounded-full shadow-md z-10 flex items-center justify-center text-blue-600 hover:text-blue-800 hover:shadow-lg transition-all md:flex hidden" title="Inverser">
                            <i class="bi bi-arrow-left-right"></i>
                        </button>
                        
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-wide">Départ</label>
                            <div class="relative">
                                <i class="bi bi-geo-alt absolute left-3 top-3.5 text-slate-400"></i>
                                <select name="ville_depart" id="ville_depart" class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-lg text-slate-900 font-medium focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:bg-white transition-colors appearance-none cursor-pointer" required>
                                    <option value="" class="text-slate-400">Ville de départ</option>
                                    @foreach($villes as $ville)
                                        <option value="{{ $ville->id }}" {{ old('ville_depart') == $ville->id ? 'selected' : '' }}>
                                            {{ $ville->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="space-y-1">
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-wide">Arrivée</label>
                            <div class="relative">
                                <i class="bi bi-pin-map absolute left-3 top-3.5 text-slate-400"></i>
                                <select name="ville_arrivee" id="ville_arrivee" class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-lg text-slate-900 font-medium focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:bg-white transition-colors appearance-none cursor-pointer" required>
                                    <option value="" class="text-slate-400">Ville d'arrivée</option>
                                    @foreach($villes as $ville)
                                        <option value="{{ $ville->id }}" {{ old('ville_arrivee') == $ville->id ? 'selected' : '' }}>
                                            {{ $ville->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-wide">Date</label>
                            <div class="relative">
                                <i class="bi bi-calendar4 absolute left-3 top-3.5 text-slate-400"></i>
                                <input type="date" name="date_depart" id="date_depart" 
                                       value="{{ old('date_depart', date('Y-m-d')) }}" min="{{ date('Y-m-d') }}"
                                       class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-lg text-slate-900 font-medium focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:bg-white transition-colors" required>
                            </div>
                        </div>

                        <div class="space-y-1">
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-wide">Voyageurs</label>
                            <div class="relative">
                                <i class="bi bi-people absolute left-3 top-3.5 text-slate-400"></i>
                                <input type="number" name="nombre_voyageurs" id="nombre_voyageurs" 
                                       value="{{ old('nombre_voyageurs', 1) }}" min="1" max="10"
                                       class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-lg text-slate-900 font-medium focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:bg-white transition-colors" required>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Class Selection - Simplified -->
                    <div class="flex gap-4 p-1 bg-slate-100/50 rounded-lg">
                        <label class="flex-1 cursor-pointer">
                            <input type="radio" name="classe_bus" value="standard" class="peer sr-only" checked>
                            <div class="text-center py-2 rounded-md text-sm font-medium text-slate-600 peer-checked:bg-white peer-checked:text-blue-600 peer-checked:shadow-sm transition-all hover:text-blue-500">Standard</div>
                        </label>
                        <label class="flex-1 cursor-pointer">
                            <input type="radio" name="classe_bus" value="confort" class="peer sr-only">
                            <div class="text-center py-2 rounded-md text-sm font-medium text-slate-600 peer-checked:bg-white peer-checked:text-blue-600 peer-checked:shadow-sm transition-all hover:text-blue-500">Confort</div>
                        </label>
                        <label class="flex-1 cursor-pointer">
                            <input type="radio" name="classe_bus" value="premium" class="peer sr-only">
                            <div class="text-center py-2 rounded-md text-sm font-medium text-slate-600 peer-checked:bg-white peer-checked:text-blue-600 peer-checked:shadow-sm transition-all hover:text-blue-500">Premium</div>
                        </label>
                    </div>

                    <button type="submit" class="w-full bg-blue-600 text-white font-bold py-4 rounded-xl hover:bg-blue-700 shadow-lg shadow-blue-500/30 transition-all transform hover:-translate-y-0.5 focus:ring-4 focus:ring-blue-200">
                        Rechercher
                    </button>
                    
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function swapCities() {
        const depart = document.getElementById('ville_depart');
        const arrivee = document.getElementById('ville_arrivee');
        const temp = depart.value;
        depart.value = arrivee.value;
        arrivee.value = temp;
    }
</script>
@endpush
