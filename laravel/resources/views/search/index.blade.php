@extends('layouts.app')

@section('title', 'Réservez votre billet - SATAS')

@section('content')
<div class="min-h-screen bg-slate-50 flex flex-col font-sans">
    
    <!-- Navbar -->
    <nav class="bg-white shadow-sm border-b border-slate-100 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-20 items-center">
                <!-- Logo -->
                <div class="flex-shrink-0 flex items-center">
                    <a href="{{ route('home') }}" class="flex items-center gap-2">
                        <div class="bg-blue-600 text-white p-2 rounded-lg">
                            <i class="bi bi-bus-front-fill text-xl"></i>
                        </div>
                        <span class="text-2xl font-black text-blue-900 tracking-tight">SATAS</span>
                    </a>
                </div>

                <!-- Desktop Menu -->
                <div class="hidden md:flex space-x-6 items-center">
                    <a href="{{ route('home') }}" class="text-slate-600 hover:text-blue-600 font-medium transition-colors">Accueil</a>
                    <a href="#" class="text-slate-600 hover:text-blue-600 font-medium transition-colors">Nos Lignes</a>
                    <a href="#" class="text-slate-600 hover:text-blue-600 font-medium transition-colors">Contact</a>

                    @auth
                        <div class="flex items-center gap-3">
                            <span class="text-slate-700 font-medium text-sm">
                                <i class="bi bi-person-circle text-blue-600"></i> {{ Auth::user()->name }}
                            </span>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="px-4 py-2 border border-slate-300 text-slate-600 text-sm font-medium rounded-lg hover:bg-red-50 hover:border-red-300 hover:text-red-600 transition-all">
                                    <i class="bi bi-box-arrow-right"></i> Déconnexion
                                </button>
                            </form>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="text-slate-600 hover:text-blue-600 font-medium transition-colors text-sm">Connexion</a>
                        <a href="{{ route('register') }}" class="px-5 py-2.5 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-700 transition-all shadow-md shadow-blue-200 text-sm">
                            S'inscrire
                        </a>
                    @endauth
                </div>

                <!-- Mobile Menu Button -->
                <div class="md:hidden flex items-center">
                    <button class="text-slate-500 hover:text-blue-600 focus:outline-none">
                        <i class="bi bi-list text-3xl"></i>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Content -->
    <div class="flex-grow flex items-center justify-center p-4 md:p-8">
        <div class="w-full max-w-6xl grid md:grid-cols-2 gap-12 items-center">
            
            <!-- Left Side: Text -->
            <div class="space-y-6 text-center md:text-left animate-fade-in-up">
                <h1 class="text-4xl md:text-6xl font-extrabold text-slate-900 leading-tight">
                    Voyagez à travers le <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-blue-400">Maroc</span> en toute sécurité.
                </h1>
                <p class="text-lg text-slate-600 max-w-lg mx-auto md:mx-0 leading-relaxed">
                    SATAS vous accompagne depuis des années. Profitez d'un confort optimal et d'un service ponctuel pour tous vos déplacements.
                </p>
                
                <div class="pt-4 flex justify-center md:justify-start gap-4">
                    <a href="#search-form" class="hidden md:inline-flex items-center gap-2 text-blue-600 font-semibold hover:text-blue-700 transition-colors">
                        Voir nos destinations <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>

            <!-- Right Side: Search Form -->
            <div id="search-form" class="bg-white rounded-2xl shadow-xl border border-slate-100 p-8 transform transition-all hover:shadow-2xl">
                <div class="mb-6 border-b border-slate-100 pb-4">
                    <h2 class="text-2xl font-bold text-slate-800">Réservez votre place</h2>
                    <p class="text-slate-500 text-sm">Simple, Rapide et Sécurisé.</p>
                </div>

                @if ($errors->any())
                    <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-r">
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
                        <!-- Swap Button -->
                        <div class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 z-10 hidden md:block">
                            <button type="button" onclick="swapCities()" class="w-10 h-10 bg-white border border-slate-200 rounded-full shadow hover:shadow-md flex items-center justify-center text-blue-600 transition-transform hover:rotate-180" title="Inverser">
                                <i class="bi bi-arrow-left-right"></i>
                            </button>
                        </div>
                        
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-wide ml-1">Départ</label>
                            <div class="relative group">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="bi bi-geo-alt text-slate-400 group-focus-within:text-blue-500 transition-colors"></i>
                                </div>
                                <select name="ville_depart" id="ville_depart" class="block w-full pl-10 pr-3 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:bg-white transition-all appearance-none cursor-pointer font-medium" required>
                                    <option value="" class="text-slate-400">Ville de départ</option>
                                    @foreach($villes as $ville)
                                        <option value="{{ $ville->id }}" {{ old('ville_depart') == $ville->id ? 'selected' : '' }}>
                                            {{ $ville->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-wide ml-1">Arrivée</label>
                            <div class="relative group">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="bi bi-pin-map text-slate-400 group-focus-within:text-blue-500 transition-colors"></i>
                                </div>
                                <select name="ville_arrivee" id="ville_arrivee" class="block w-full pl-10 pr-3 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:bg-white transition-all appearance-none cursor-pointer font-medium" required>
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
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-wide ml-1">Date</label>
                            <div class="relative group">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="bi bi-calendar4 text-slate-400 group-focus-within:text-blue-500 transition-colors"></i>
                                </div>
                                <input type="date" name="date_depart" id="date_depart" 
                                       value="{{ old('date_depart', date('Y-m-d')) }}" min="{{ date('Y-m-d') }}"
                                       class="block w-full pl-10 pr-3 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:bg-white transition-all font-medium" required>
                            </div>
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-500 uppercase tracking-wide ml-1">Voyageurs</label>
                            <div class="relative group">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="bi bi-people text-slate-400 group-focus-within:text-blue-500 transition-colors"></i>
                                </div>
                                <input type="number" name="nombre_voyageurs" id="nombre_voyageurs" 
                                       value="{{ old('nombre_voyageurs', 1) }}" min="1" max="10"
                                       class="block w-full pl-10 pr-3 py-3 bg-slate-50 border border-slate-200 rounded-xl text-slate-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 focus:bg-white transition-all font-medium" required>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Class Selection - Modern Pills -->
                    <div class="bg-slate-50 p-1.5 rounded-xl border border-slate-100 flex">
                        <label class="flex-1 cursor-pointer">
                            <input type="radio" name="classe_bus" value="standard" class="peer sr-only" checked>
                            <div class="text-center py-2.5 rounded-lg text-sm font-semibold text-slate-500 peer-checked:bg-white peer-checked:text-blue-600 peer-checked:shadow-sm transition-all">Standard</div>
                        </label>
                        <label class="flex-1 cursor-pointer">
                            <input type="radio" name="classe_bus" value="confort" class="peer sr-only">
                            <div class="text-center py-2.5 rounded-lg text-sm font-semibold text-slate-500 peer-checked:bg-white peer-checked:text-blue-600 peer-checked:shadow-sm transition-all">Confort</div>
                        </label>
                        <label class="flex-1 cursor-pointer">
                            <input type="radio" name="classe_bus" value="premium" class="peer sr-only">
                            <div class="text-center py-2.5 rounded-lg text-sm font-semibold text-slate-500 peer-checked:bg-white peer-checked:text-blue-600 peer-checked:shadow-sm transition-all flex justify-center items-center gap-1">
                                Premium 
                            </div>
                        </label>
                    </div>

                    <button type="submit" class="w-full bg-blue-600 text-white font-bold py-4 rounded-xl hover:bg-blue-700 shadow-lg shadow-blue-600/20 transition-all transform hover:-translate-y-0.5 active:translate-y-0 text-lg flex justify-center items-center gap-2">
                        <i class="bi bi-search"></i> Rechercher
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Footer Simple -->
    <div class="bg-white border-t border-slate-100 py-6">
        <div class="max-w-7xl mx-auto px-4 text-center text-slate-400 text-sm">
            &copy; {{ date('Y') }} SATAS Maroc. Tous droits réservés.
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
<style>
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in-up {
        animation: fadeInUp 0.8s ease-out;
    }
</style>
@endpush
