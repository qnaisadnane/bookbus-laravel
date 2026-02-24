@extends('layouts.app')

@section('title', 'Connexion - BookBus Maroc')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-slate-50 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        
        <!-- Logo and Header -->
        <div class="text-center">
            <a href="{{ route('home') }}" class="inline-block">
                <h1 class="text-4xl font-bold bg-gradient-to-r from-blue-600 to-blue-800 bg-clip-text text-transparent">
                    BookBus
                </h1>
                <p class="text-sm text-slate-600 mt-1">Réservez votre voyage en toute simplicité</p>
            </a>
        </div>

        <!-- Login Card -->
        <div class="bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
            <div class="px-8 py-10">
                @php
                    $fromBooking = str_contains(session()->previousUrl() ?? '', 'booking') 
                                  || str_contains(url()->previous() ?? '', 'search');
                @endphp

                @if($fromBooking || session('booking_redirect'))
                <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-xl flex items-start gap-3">
                    <div class="text-blue-600 mt-0.5 text-lg"><i class="bi bi-ticket-perforated-fill"></i></div>
                    <div>
                        <p class="text-sm font-semibold text-blue-800">Finaliser votre réservation</p>
                        <p class="text-xs text-blue-600 mt-0.5">Connectez-vous pour accéder à votre réservation.</p>
                    </div>
                </div>
                @endif

                <div class="mb-8">
                    <h2 class="text-2xl font-bold text-slate-900">Bienvenue</h2>
                    <p class="text-slate-600 mt-1">Connectez-vous à votre compte</p>
                </div>

                <!-- Session Status -->
                @if (session('status'))
                    <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                        <p class="text-sm text-green-700">{{ session('status') }}</p>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="space-y-6">
                    @csrf

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-semibold text-slate-700 mb-2">
                            Adresse email
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="bi bi-envelope text-slate-400"></i>
                            </div>
                            <input 
                                id="email" 
                                type="email" 
                                name="email" 
                                value="{{ old('email') }}" 
                                required 
                                autofocus 
                                autocomplete="username"
                                class="block w-full pl-10 pr-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors @error('email') border-red-500 @enderror"
                                placeholder="votre@email.com"
                            >
                        </div>
                        @error('email')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-semibold text-slate-700 mb-2">
                            Mot de passe
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="bi bi-lock text-slate-400"></i>
                            </div>
                            <input 
                                id="password" 
                                type="password" 
                                name="password" 
                                required 
                                autocomplete="current-password"
                                class="block w-full pl-10 pr-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors @error('password') border-red-500 @enderror"
                                placeholder="••••••••"
                            >
                        </div>
                        @error('password')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Remember Me & Forgot Password -->
                    <div class="flex items-center justify-between">
                        <label class="flex items-center">
                            <input 
                                type="checkbox" 
                                name="remember" 
                                class="w-4 h-4 text-blue-600 border-slate-300 rounded focus:ring-blue-500"
                            >
                            <span class="ml-2 text-sm text-slate-600">Se souvenir de moi</span>
                        </label>

                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="text-sm font-medium text-blue-600 hover:text-blue-700">
                                Mot de passe oublié ?
                            </a>
                        @endif
                    </div>

                    <!-- Submit Button -->
                    <button 
                        type="submit" 
                        class="w-full bg-gradient-to-r from-blue-600 to-blue-700 text-white font-bold py-3 px-4 rounded-lg hover:from-blue-700 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all shadow-lg shadow-blue-500/30"
                    >
                        Se connecter
                    </button>
                </form>
            </div>

            <!-- Register Link -->
            <div class="px-8 py-4 bg-slate-50 border-t border-slate-200">
                <p class="text-center text-sm text-slate-600">
                    Vous n'avez pas de compte ?
                    <a href="{{ route('register') }}" class="font-semibold text-blue-600 hover:text-blue-700">
                        Créer un compte
                    </a>
                </p>
            </div>
        </div>

        <!-- Back to Home -->
        <div class="text-center">
            <a href="{{ route('home') }}" class="inline-flex items-center text-sm text-slate-600 hover:text-slate-900">
                <i class="bi bi-arrow-left mr-2"></i>
                Retour à l'accueil
            </a>
        </div>
    </div>
</div>
@endsection
