# Guide de déploiement SATAS Bus - Plateforme de Réservation

## 📋 Étapes de mise en place

### 1. Installation des dépendances
```bash
cd laravel
composer install
npm install
```

### 2. Configuration de l'environnement
```bash
cp .env.example .env
php artisan key:generate
```

Editez le fichier `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bookbus
DB_USERNAME=root
DB_PASSWORD=
```

### 3. Création et migration de la base de données
```bash
# Créer la base de données (PhpMyAdmin ou ligne de commande)
mysql -u root -e "CREATE DATABASE bookbus;"

# Exécuter les migrations
php artisan migrate:fresh --seed

# Cela va:
# - Créer toutes les tables
# - Seed le SatasSeeder qui ajoute:
#   * 10 lignes SATAS (L101 à L110)
#   * 20 bus SATAS avec immatriculations réelles
#   * 15 chauffeurs SATAS
#   * Tarifs segmentés pour chaque segment
#   * Horaires pour chaque ligne
```

### 4. Build des assets
```bash
npm run build
# ou en développement:
npm run dev
```

### 5. Lancement de l'application
```bash
# Terminal 1: Serveur Laravel
php artisan serve

# Terminal 2: Compilation vite (développement)
npm run dev

# Terminal 3 (optionnel): Queue listeners
php artisan queue:listen
```

L'application sera accessible sur `http://localhost:8000`

---

## 🧪 Tests

### Exécuter tous les tests
```bash
php artisan test
```

### Tests spécifiques SATAS
```bash
php artisan test tests/Feature/SatasBookingTest.php
php artisan test tests/Feature/SatasBookingApiTest.php
```

---

## 📊 Modèle de données SATAS

### Structure clé

**Tarification segmentée:**
- Chaque segment (ex: Casa→Settat) a UN prix indépendant
- Casa→Marrakech ≠ (Casa→Settat) + (Settat→Marrakech)
- Les prix varient par type de bus (Standard, Confort +10%, Premium +20%)

**Relations principales:**
```
Route (L101) 
  ├── Stop (Gare Casa, Gare Settat, Gare Marrakech)
  ├── Segment (Casa→Settat, Settat→Marrakech)
  │   └── Fare (tarif spécifique par bus type)
  └── Schedule (horaires: lundi 8h, lundi 14h, etc.)
      └── Trip (instance du 10/02/2026)
          ├── Assignment (Bus + Chauffeur)
          └── Booking (réservation client)
```

---

## 🎯 Fonctionnalités principales

### 1. Recherche intelligente
- Recherche par gares de départ/arrivée
- Filtre par type de bus (Standard, Confort, Premium)
- Services (Wi-Fi, Prises USB, WC)
- Affiche uniquement les trajets SATAS

### 2. Processus de réservation
- Sélection du segment (Casa→Marrakech, etc.)
- Entrée des passagers (prénom, nom, email, téléphone)
- **Options SATAS:**
  - Snack-box (15 MAD)
  - Assurance annulation (5-8% du tarif)
  - Codes promotionnels (SATAS10, SATAS15, SATAS20, LOYALTY5)
- Tarif final = (Tarif segment × Multiplicateur bus type) + Options - Remise

### 3. Tableau de bord administrateur
- **Trajets:** Création, affectation bus/chauffeur, annulation
- **Bus:** Gestion statut (service, maintenance, hors-service)
- **Chauffeurs:** Suivi heures (max 10h/jour)
- **Statistiques:** Occupancy, revenus, performance

### 4. Politiques SATAS
- **Capacité:** Respectée par bus type (Standard: 40, Confort: 35, Premium: 30)
- **Annulation >24h:** Remboursement 100%
- **Annulation <24h:** Remboursement 50%
- **Bus un trajet à la fois:** Empêche double booking
- **Permis valides:** Chauffeurs doivent avoir permis valide

---

## 📚 Routes API

### Publiques
- `GET /` - Page d'accueil / Recherche
- `GET /search` - Résultats de recherche
- `POST /booking/create` - Formulaire de réservation
- `POST /booking/store` - Enregistrement réservation
- `GET /booking/{id}/confirmation` - Confirmation

### Administrateur (authentification requise)
- `GET /admin/dashboard` - Dashboard
- `GET /admin/trips` - Liste trajets
- `POST /admin/trips/{id}/assign-resources` - Affecter bus/chauffeur
- `GET /admin/buses` - Gestion parc
- `POST /admin/buses/{id}/status` - Changer statut bus

---

## 🔧 Configuration requise

- **PHP:** 8.2+
- **MySQL:** 5.7+ ou MariaDB 10.3+
- **Node.js:** 18+
- **XAMPP/WAMP/LAMP:** Installation locale

---

## 📝 Vérifications essentielles

### Avant mise en production:

1. ✅ Migrations exécutées (`php artisan migrate`)
2. ✅ Seeders lancés (`php artisan db:seed`)
3. ✅ Tests passant (`php artisan test`)
4. ✅ Assets compilés (`npm run build`)
5. ✅ Logs configurés (voir `config/logging.php`)
6. ✅ Email configurable (voir `config/mail.php`)
7. ✅ Authentification activée (roles admin)

---

## 🐛 Debugging

### Fichiers logs
```bash
tail -f storage/logs/laravel.log
```

### Mode debug
Mettez `APP_DEBUG=true` dans `.env` (développement uniquement)

### Erreurs courantes

**"SQLSTATE[HY000]: General error: 1030"**
- Vérifier l'espace disque
- Vérifier les permissions du dossier `storage/`

**"Route not found"**
- Vérifier le contrôleur existe
- `php artisan route:list` pour lister toutes les routes

---

## 📞 Support

Pour les questions sur:
- **Architecture:** Voir `documentation.md`
- **Base de données:** Voir migrations dans `database/migrations/`
- **Services:** Voir `app/Services/`
- **Tests:** Voir `tests/Feature/`
