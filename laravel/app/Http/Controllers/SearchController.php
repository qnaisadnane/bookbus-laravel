<?php

namespace App\Http\Controllers;

use App\Services\SearchService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    public function __construct(
        protected SearchService $searchService
    ) {}

    /**
     * Afficher le formulaire de recherche (Route GET)
     */
    public function index(): View
    {
        // Récupérer uniquement les villes qui ont des gares SATAS
        $villes = DB::table('ville')
            ->join('stations', 'ville.id', '=', 'stations.city_id')
            ->select('ville.id', 'ville.name')
            ->distinct()
            ->orderBy('ville.name') // Ordre alphabétique
            ->get();

        return view('search.index', compact('villes'));
    }

    /**
     * Traiter la recherche et afficher les résultats (Route GET)
     */
    public function search(Request $request)
    {
        // ÉTAPE 1 : Valider les entrées utilisateur
        $validated = $request->validate([
            'ville_depart' => 'required|exists:ville,id',
            'ville_arrivee' => 'required|exists:ville,id|different:ville_depart',
            'date_depart' => 'required|date|after_or_equal:today',
            'nombre_voyageurs' => 'required|integer|min:1|max:10',
            
            // Filtres optionnels
            'classe_bus' => 'nullable|in:standard,confort,premium',
            'heure_depart' => 'nullable|in:matin,apres-midi,soir',
            'prix_max' => 'nullable|numeric|min:50|max:500',
            'tri' => 'nullable|in:prix_asc,prix_desc,heure_asc,duree_asc',
        ], [
            'ville_depart.required' => 'Veuillez sélectionner une ville de départ',
            'ville_depart.exists' => 'Cette ville n\'existe pas',
            'ville_arrivee.required' => 'Veuillez sélectionner une ville d\'arrivée',
            'ville_arrivee.exists' => 'Cette ville n\'existe pas',
            'ville_arrivee.different' => 'La ville d\'arrivée doit être différente de la ville de départ',
            'date_depart.required' => 'Veuillez sélectionner une date',
            'date_depart.date' => 'Format de date invalide',
            'date_depart.after_or_equal' => 'La date doit être aujourd\'hui ou dans le futur',
            'nombre_voyageurs.required' => 'Veuillez indiquer le nombre de voyageurs',
            'nombre_voyageurs.integer' => 'Le nombre de voyageurs doit être un nombre entier',
            'nombre_voyageurs.min' => 'Minimum 1 voyageur',
            'nombre_voyageurs.max' => 'Maximum 10 voyageurs',
        ]);

        $villeDepart = $validated['ville_depart'];
        $villeArrivee = $validated['ville_arrivee'];
        $dateDepart = $validated['date_depart'];
        $nombreVoyageurs = $validated['nombre_voyageurs'];

        // ÉTAPE 2 : Trouver les segments qui correspondent
        $trajets = $this->rechercherTrajets(
            $villeDepart, 
            $villeArrivee, 
            $dateDepart, 
            $nombreVoyageurs
        );

        \Illuminate\Support\Facades\Log::info("SEARCH DEBUG: Dep=$villeDepart, Arr=$villeArrivee, Date=$dateDepart, Count=" . $trajets->count());

        // ÉTAPE 3 : Appliquer les filtres
        $trajets = $this->appliquerFiltres($trajets, $request);

        // ÉTAPE 4 : Calculer les prix selon la classe
        $trajets = $this->calculerPrixParClasse($trajets, $request->classe_bus);

        // ÉTAPE 5 : Trier les résultats
        $trajets = $this->trierResultats($trajets, $request->tri);

        // Récupérer les informations des villes
        $nomVilleDepart = DB::table('ville')->where('id', $villeDepart)->value('name');
        $nomVilleArrivee = DB::table('ville')->where('id', $villeArrivee)->value('name');

        // Suggestions de dates alternatives si pas de résultats
        $datesAlternatives = [];
        if ($trajets->isEmpty()) {
            $datesAlternatives = $this->suggererDatesAlternatives($villeDepart, $villeArrivee, $dateDepart);
        }

        return view('search.results', compact(
            'trajets',
            'nomVilleDepart',
            'nomVilleArrivee',
            'dateDepart',
            'nombreVoyageurs',
            'datesAlternatives'
        ));
    }

    /**
     * ALGORITHME DE RECHERCHE
     * 
     * Comprendre le problème :
     * - Un utilisateur cherche un trajet du point A au point B
     * - SATAS ne propose pas forcément un segment direct A→B
     * - Il faut trouver tous les segments qui relient A à B, directement ou indirectement
     */
    private function rechercherTrajets($villeDepart, $villeArrivee, $dateDepart, $nombreVoyageurs)
    {
        // Get departure and arrival stations
        $departureStation = DB::table('stations')->where('city_id', $villeDepart)->first();
        $arrivalStation = DB::table('stations')->where('city_id', $villeArrivee)->first();

        if (!$departureStation || !$arrivalStation) {
            \Illuminate\Support\Facades\Log::info("SEARCH ERROR: Stations not found for Cities $villeDepart, $villeArrivee");
            return collect();
        }

        \Illuminate\Support\Facades\Log::info("SEARCH STATIONS: Dep={$departureStation->id}, Arr={$arrivalStation->id}");

        // Get stops for these stations
        $departureStops = DB::table('stops')->where('station_id', $departureStation->id)->get();
        $arrivalStops = DB::table('stops')->where('station_id', $arrivalStation->id)->get();

        if ($departureStops->isEmpty() || $arrivalStops->isEmpty()) {
            \Illuminate\Support\Facades\Log::info("SEARCH ERROR: No stops found for stations. Dep stops: " . $departureStops->count() . ", Arr stops: " . $arrivalStops->count());
            return collect();
        }

        $dateObj = Carbon::createFromFormat('Y-m-d', $dateDepart);
        $dayOfWeek = strtolower($dateObj->format('l'));

        // Get all unique routes from departure stops
        $routeIds = $departureStops->pluck('route_id')->unique();

        $trajets = collect();

        // For each route
        foreach ($routeIds as $routeId) {
            // Get all stops on this route in order
            $stops = DB::table('stops')
                ->where('route_id', $routeId)
                ->orderBy('order')
                ->get();

            // Find departure and arrival stops on this route
            $depStop = $departureStops->where('route_id', $routeId)->first();
            $arrStop = $arrivalStops->where('route_id', $routeId)->first();

            if (!$depStop || !$arrStop || $depStop->order >= $arrStop->order) {
                continue; // Skip if not on same route or wrong order
            }

            // Get schedules for this route on this day
            $schedules = DB::table('schedules')
                ->where('route_id', $routeId)
                ->where('day_of_week', $dayOfWeek)
                ->where('active', true)
                ->get();

            // Get or create trips for this date
            foreach ($schedules as $schedule) {
                $trips = DB::table('trips')
                    ->where('schedule_id', $schedule->id)
                    ->where('departure_date', $dateDepart)
                    ->where('status', '!=', 'cancelled')
                    ->whereNotNull('bus_id')
                    ->get();

                foreach ($trips as $trip) {
                    // Get fare for this segment (from departure stop to arrival stop)
                    $segment = DB::table('segments')
                        ->where('route_id', $routeId)
                        ->where('departure_stop_id', $depStop->id)
                        ->where('arrival_stop_id', $arrStop->id)
                        ->first();

                    // If direct segment doesn't exist, find all segments in between
                    if (!$segment) {
                        // Find all intermediate segments
                        $allSegments = DB::table('segments')
                            ->where('route_id', $routeId)
                            ->whereIn('departure_stop_id', $stops->where('order', '>=', $depStop->order)->where('order', '<', $arrStop->order)->pluck('id'))
                            ->get();
                        
                        if ($allSegments->isEmpty()) {
                            continue; // No segments found
                        }
                        
                        // Use the first segment for booking reference
                        $segment = $allSegments->first();
                    } else {
                        $allSegments = collect([$segment]);
                    }

                    // Get bus info
                    $bus = DB::table('bus')->where('id', $trip->bus_id)->first();
                    if (!$bus) continue;

                    // Count existing bookings for this trip
                    $placesReservees = DB::table('bookings')
                        ->where('trip_id', $trip->id)
                        ->count() ?? 0;

                    $placesDisponibles = max(0, $bus->capacity - $placesReservees);

                    // Calculate total fare from all segments
                    $fare = 0;
                    foreach ($allSegments as $seg) {
                        $segFare = DB::table('fares')
                            ->where('segment_id', $seg->id)
                            ->where('bus_type', $bus->type ?? 'standard')
                            ->where('active', true)
                            ->latest('effective_from')
                            ->value('price') ?? 0;
                        $fare += $segFare;
                    }

                    // Get route info
                    $route = DB::table('route')->where('id', $routeId)->first();

                    $trajets->push((object)[
                        'trip_id' => $trip->id,
                        'schedule_id' => $schedule->id,
                        'route_id' => $routeId,
                        'departure_time' => $schedule->departure_time,
                        'arrival_time' => $schedule->arrival_time,
                        'segment_id' => $segment->id, // Always has a value now
                        'distance_km' => $allSegments->sum('distance_km') ?? 0,
                        'bus_id' => $trip->bus_id,
                        'departure_date' => $trip->departure_date,
                        'places_disponibles' => $placesDisponibles,
                        'peut_reserver' => $placesDisponibles >= $nombreVoyageurs,
                        'fare' => $fare,
                        'matricule' => $bus->registration_number ?? 'BUS-' . $trip->bus_id,
                        'bus_name' => $bus->model ?? 'SATAS Bus',
                        'bus_type' => $bus->type ?? 'standard',
                        'route_nom' => $route->nom ?? 'Route ' . $routeId,
                        'duree_minutes' => Carbon::createFromFormat('H:i:s', $schedule->departure_time)->diffInMinutes(Carbon::createFromFormat('H:i:s', $schedule->arrival_time)),
                    ]);
                }
            }
        }

        return $trajets;
    }

    /**
     * Appliquer les filtres sur les résultats
     */
    private function appliquerFiltres($trajets, $request)
    {
        // Filtre par heure de départ
        if ($request->heure_depart) {
            $trajets = $trajets->filter(function($trajet) use ($request) {
                $heure = Carbon::createFromFormat('H:i:s', $trajet->heure_depart_stop)->format('H');
                
                switch ($request->heure_depart) {
                    case 'matin': // 5h-12h
                        return $heure >= 5 && $heure < 12;
                    case 'apres-midi': // 12h-18h
                        return $heure >= 12 && $heure < 18;
                    case 'soir': // 18h-00h
                        return $heure >= 18 || $heure < 5;
                    default:
                        return true;
                }
            });
        }

        // Filtre par prix maximum (appliqué après calcul de la classe)
        if ($request->prix_max) {
            $trajets = $trajets->filter(function($trajet) use ($request) {
                return $trajet->prix_final <= $request->prix_max;
            });
        }

        return $trajets;
    }

    /**
     * Calculer les prix selon la classe de bus
     */
    private function calculerPrixParClasse($trajets, $classe = 'standard')
    {
        $multiplicateurs = [
            'standard' => 1.0,    // Prix de base
            'confort' => 1.1,     // +10%
            'premium' => 1.2,     // +20%
        ];

        $multiplicateur = $multiplicateurs[$classe ?? 'standard'];

        return $trajets->map(function($trajet) use ($multiplicateur, $classe) {
            $trajet->classe_bus = $classe ?? 'standard';
            $trajet->prix_base = $trajet->fare;
            $trajet->prix_final = round($trajet->fare * $multiplicateur, 2);
            
            // Ajouter les avantages selon la classe
            $trajet->avantages = $this->getAvantagesClasse($classe ?? 'standard');
            
            return $trajet;
        });
    }

    /**
     * Obtenir les avantages selon la classe
     */
    private function getAvantagesClasse($classe)
    {
        $avantages = [
            'standard' => ['Siège confortable', 'Climatisation'],
            'confort' => ['Siège confortable', 'Climatisation', 'Plus d\'espace jambes', 'Collation'],
            'premium' => ['Siège confortable', 'Climatisation', 'Plus d\'espace jambes', 'Collation', 'Wi-Fi gratuit', 'Prises USB'],
        ];

        return $avantages[$classe] ?? $avantages['standard'];
    }

    /**
     * Trier les résultats selon le critère choisi
     */
    private function trierResultats($trajets, $tri = 'prix_asc')
    {
        switch ($tri) {
            case 'prix_asc':
                return $trajets->sortBy('prix_final')->values();
            
            case 'prix_desc':
                return $trajets->sortByDesc('prix_final')->values();
            
            case 'heure_asc':
                return $trajets->sortBy('heure_depart_stop')->values();
            
            case 'duree_asc':
                return $trajets->sortBy('duree_minutes')->values();
            
            default:
                return $trajets->sortBy('prix_final')->values();
        }
    }

    /**
     * Suggérer des dates alternatives si aucun résultat
     */
    private function suggererDatesAlternatives($villeDepart, $villeArrivee, $dateOrigine)
    {
        $gareDepart = DB::table('gares')->where('id_ville', $villeDepart)->first();
        $gareArrivee = DB::table('gares')->where('id_ville', $villeArrivee)->first();

        if (!$gareDepart || !$gareArrivee) {
            return [];
        }

        $suggestions = [];
        $dateActuelle = Carbon::parse($dateOrigine);

        // Chercher les 7 prochains jours
        for ($i = 1; $i <= 7; $i++) {
            $dateTest = $dateActuelle->copy()->addDays($i);
            
            $count = DB::table('route')
                ->join('etapes as etape_depart', function($join) use ($gareDepart) {
                    $join->on('route.id', '=', 'etape_depart.route_id')
                         ->where('etape_depart.gare_id', '=', $gareDepart->id);
                })
                ->join('etapes as etape_arrivee', function($join) use ($gareArrivee) {
                    $join->on('route.id', '=', 'etape_arrivee.route_id')
                         ->where('etape_arrivee.gare_id', '=', $gareArrivee->id);
                })
                ->join('programmes', 'route.id', '=', 'programmes.id_route')
                ->where('etape_depart.ordre', '<', 'etape_arrivee.ordre')
                ->where('programmes.jour_depart', $dateTest->format('Y-m-d'))
                ->count();

            if ($count > 0) {
                $suggestions[] = [
                    'date' => $dateTest->format('Y-m-d'),
                    'date_formatee' => $dateTest->locale('fr')->isoFormat('dddd DD MMMM'),
                    'nombre_trajets' => $count
                ];

                // Limiter à 3 suggestions
                if (count($suggestions) >= 3) {
                    break;
                }
            }
        }

        return $suggestions;
    }

    /**
     * Afficher les détails complets d'une route
     */
    public function details($routeId)
    {
        $route = DB::table('route')->where('id', $routeId)->first();

        if (!$route) {
            abort(404, 'Trajet non trouvé');
        }

        // Récupérer toutes les étapes avec leurs segments
        $etapes = DB::table('etapes')
            ->join('gares', 'etapes.gare_id', '=', 'gares.id')
            ->join('ville', 'gares.id_ville', '=', 'ville.id')
            ->leftJoin('segments', 'etapes.segment_id', '=', 'segments.id')
            ->where('etapes.route_id', $routeId)
            ->orderBy('etapes.ordre')
            ->select(
                'ville.name as ville_nom',
                'gares.nom as gare_nom',
                'gares.adresse',
                'etapes.heure_passage',
                'etapes.ordre',
                'segments.tarif',
                'segments.distance_km'
            )
            ->get();

        return view('search.details', compact('route', 'etapes'));
    }
}