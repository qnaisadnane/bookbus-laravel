# 🔹 Analyse du domaine - MarKoub.ma

##  1️⃣ Compréhension du domaine

marKoub.ma est une plateforme de réservation en ligne de billets de bus qui connecte :

🧍 Les voyageurs (clients)

🚌 Les compagnies de transport

🖥️ Les administrateurs de la plateforme

Le but principal est de permettre à un utilisateur de :

Chercher un trajet → Choisir un bus → Réserver un siège → Payer → Recevoir son billet

##  2️⃣ Processus de réservation (côté utilisateur)

Voici le parcours typique d’un voyageur :

 ### 🔹 Étape 1 : Recherche de voyage

L’utilisateur :

Choisit :

Ville de départ

Ville d’arrivée

Date du voyage

Lance la recherche

➡️ Le système affiche une liste des voyages disponibles.

 ### 🔹 Étape 2 : Consultation des trajets

Pour chaque voyage, l’utilisateur peut voir :

Compagnie de bus

Heure de départ et d’arrivée

Durée du trajet

Prix du billet

Nombre de places restantes

 ### 🔹 Étape 3 : Sélection du voyage

L’utilisateur :

Choisit un trajet

Sélectionne un ou plusieurs sièges disponibles

 ### 🔹 Étape 4 : Authentification

Avant de réserver :

Soit il se connecte

Soit il crée un compte

Données généralement demandées :

Nom

Email

Téléphone

 ### 🔹 Étape 5 : Réservation

L’utilisateur confirme :

Le voyage choisi

Les sièges

Ses informations personnelles

➡️ Une réservation est créée avec un statut :
En attente de paiement

 ### 🔹 Étape 6 : Paiement

L’utilisateur effectue un paiement en ligne.

Après paiement :

Le statut de la réservation devient : Confirmée

Un billet électronique est généré

 ### 🔹 Étape 7 : Billet

L’utilisateur peut :

Voir son billet

Le télécharger

Le présenter au moment du voyage

 ####  3️⃣ Entités principales identifiées

D’après l’analyse, voici les objets métier essentiels :

Entité	Description
Utilisateur	Personne qui réserve des billets (client ou admin)
Compagnie	Société de transport qui propose des voyages
Bus	Véhicule appartenant à une compagnie
Voyage (Trajet)	Déplacement d’une ville à une autre à une date précise
Ville	Lieu de départ ou d’arrivée
Siège	Place dans un bus
Réservation	Action de réserver un ou plusieurs sièges
Billet	Preuve de réservation après paiement
Paiement	Transaction liée à une réservation
####  4️⃣ Flux d’administration observé

L’administrateur gère la plateforme côté back-office.

#### 👨‍💼 Gestion des compagnies

L’admin peut :

Ajouter une compagnie

Modifier ses informations

Activer/désactiver une compagnie

#### 🚌 Gestion des bus

Pour chaque compagnie :

Ajouter des bus

Définir :

Numéro du bus

Nombre total de sièges

####  🗺️ Gestion des villes

Ajouter de nouvelles villes

Modifier les noms

Gérer les destinations disponibles

####  🧭 Gestion des voyages

L’admin (ou la compagnie) peut :

Créer un voyage :

Ville départ

Ville arrivée

Date

Heure

Bus utilisé

Prix

Modifier ou annuler un voyage

####  🎟️ Gestion des réservations

L’admin peut :

Voir toutes les réservations

Vérifier :

Paiement effectué ou non

Annuler une réservation si nécessaire

####  📊 Suivi & contrôle

L’administration permet aussi :

Suivi des ventes

Nombre de billets vendus

Historique des voyages

Gestion des utilisateurs

## 🚀 Fonctionnalités Clés

### 👤 Gestion des Utilisateurs & Rôles

Le système repose sur une hiérarchie stricte à quatre niveaux :

BasicUser (Amateur) : Quota de 10 photos/mois, albums publics uniquement.

ProUser (Professionnel) : Upload illimité, albums privés, statistiques avancées (vues, likes, géolocalisation).

Moderator : Gestion des commentaires, suspension de comptes, accès au journal d'audit.

Administrator : Contrôle total du système, statistiques globales et gestion des infrastructures.

## 🚀 Fonctionnalités Clés

1. Gestion des Utilisateurs & Sécurité
- Authentification : Système de login sécurisé.

- Hachage : Utilisation de l'algorithme natif password_hash() (BCRYPT).

- Rôles : 4 niveaux d'accès (Visiteur, Auteur, Éditeur, Administrateur).

2. Moteur Éditorial
- Workflow : État de l'article évolutif (draft, published, archived).

- Multi-catégorisation : Possibilité d'assigner un article à plusieurs thématiques.

- Recherche : Moteur de recherche interne par mots-clés dans les titres et contenus.

3. Structure des Catégories
- Hiérarchie infinie : Gestion des catégories parentes et enfants.

- Validation : Empêchement strict des boucles récursives (une catégorie ne peut être son propre parent).

- Compteurs : Affichage dynamique du nombre d'articles par catégorie dans l'arborescence.

### 🖼️ Gestion des Photos

Cycle de vie complet : Brouillon → Publié → Archivé.

Métadonnées riches : Titre, description, tags, et extraction automatique des propriétés techniques (Dimensions, MIME type, taille).

Traitement d'image : Génération automatique de miniatures (thumbnails) et redimensionnement optimisé.



