<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SearchController extends Controller
{
    /**
     * Afficher le formulaire de recherche (Route GET)
     */
    public function index()
    {
        // Récupérer uniquement les villes qui ont des gares SATAS
        $villes = DB::table('ville')
            ->join('gares', 'ville.id', '=', 'gares.id_ville')
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
        // Récupérer les gares (stops) de départ et d'arrivée
        $gareDepart = DB::table('gares')->where('id_ville', $villeDepart)->first();
        $gareArrivee = DB::table('gares')->where('id_ville', $villeArrivee)->first();

        if (!$gareDepart || !$gareArrivee) {
            return collect();
        }

        // Rechercher les routes qui passent par ces deux gares
        // Un segment relie deux stops (gares) et a son propre fare
        // Utilisation de with() pour éviter le problème N+1
        $trajets = DB::table('route')
            ->join('etapes as etape_depart', function($join) use ($gareDepart) {
                $join->on('route.id', '=', 'etape_depart.route_id')
                     ->where('etape_depart.gare_id', '=', $gareDepart->id);
            })
            ->join('etapes as etape_arrivee', function($join) use ($gareArrivee) {
                $join->on('route.id', '=', 'etape_arrivee.route_id')
                     ->where('etape_arrivee.gare_id', '=', $gareArrivee->id);
            })
            ->join('programmes', 'route.id', '=', 'programmes.id_route')
            ->join('segments', 'programmes.segment_id', '=', 'segments.id')
            ->join('bus', 'segments.id_bus', '=', 'bus.id')
            ->where('etape_depart.ordre', '<', 'etape_arrivee.ordre')
            ->where('programmes.jour_depart', $dateDepart)
            ->select(
                'route.id as route_id',
                'route.nom as route_nom',
                'route.description',
                'programmes.id as programme_id',
                'programmes.heure_depart',
                'programmes.heure_arrivee',
                'segments.tarif as fare',
                'segments.distance_km',
                'segments.id as segment_id',
                'bus.id as bus_id',
                'bus.matricule',
                'bus.capacite',
                'etape_depart.heure_passage as heure_depart_stop',
                'etape_arrivee.heure_passage as heure_arrivee_stop',
                'etape_depart.gare_id as departure_stop_id',
                'etape_arrivee.gare_id as arrival_stop_id'
            )
            ->distinct()
            ->get();

        // Calculer les places disponibles et la durée pour chaque trajet
        $trajets = $trajets->map(function($trajet) use ($nombreVoyageurs) {
            // Compter les réservations existantes pour ce programme
            $placesReservees = DB::table('reservation')
                ->where('programme_id', $trajet->programme_id)
                ->sum('nombre_places') ?? 0;

            $placesDisponibles = $trajet->capacite - $placesReservees;
            $trajet->places_disponibles = $placesDisponibles;
            $trajet->peut_reserver = $placesDisponibles >= $nombreVoyageurs;

            // Calculer la durée du trajet
            $debut = Carbon::createFromFormat('H:i:s', $trajet->heure_depart_stop);
            $fin = Carbon::createFromFormat('H:i:s', $trajet->heure_arrivee_stop);
            $trajet->duree_minutes = $debut->diffInMinutes($fin);

            return $trajet;
        });

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