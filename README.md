#  HRIS Plateforme

**Plateforme de Gestion des Ressources Humaines** — Solution web complète pour la gestion RH, le suivi des collaborateurs, le recrutement, la formation, le pointage et la collaboration interne.

![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?style=flat-square&logo=laravel&logoColor=white)
![Vue.js](https://img.shields.io/badge/Vue.js-3-4FC08D?style=flat-square&logo=vue.js&logoColor=white)
![Inertia.js](https://img.shields.io/badge/Inertia.js-2-9553E9?style=flat-square&logo=inertia&logoColor=white)
![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-3-06B6D4?style=flat-square&logo=tailwindcss&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-≥8.2-777BB4?style=flat-square&logo=php&logoColor=white)

---

##  Table des Matières

- [Présentation](#-présentation)
- [Fonctionnalités](#-fonctionnalités)
- [Stack Technique](#-stack-technique)
- [Prérequis](#-prérequis)
- [Installation](#-installation)
- [Structure du Projet](#-structure-du-projet)
- [Rôles & Permissions](#-rôles--permissions)
- [Configuration Email](#-configuration-email)
- [Déploiement (OVH Mutualisé)](#-déploiement-ovh-mutualisé)
- [Commandes Utiles](#-commandes-utiles)
- [Contribution](#-contribution)
- [Licence](#-licence)

---

## Présentation

HRIS Plateforme est une application web monolithique SPA (Single Page Application) conçue pour centraliser la gestion des ressources humaines au sein d'une organisation multi-entités.

L'application utilise **Laravel 12** côté serveur, **Vue 3 + Inertia.js** côté client, et **Tailwind CSS** pour l'interface utilisateur. Elle offre une navigation fluide sans rechargement de page, un système de rôles granulaire, et des modules couvrant l'ensemble du cycle de vie RH.

---

## Fonctionnalités

### Dashboards Intelligents
- Tableaux de bord personnalisés selon le rôle (Super Admin, Admin Entité, RH, Manager, Formateur, Collaborateur, Invité)
- Widgets de statistiques, tâches récentes et indicateurs clés

### Gestion des Collaborateurs
- Fiches collaborateurs complètes (profil, rôle, entité, direction)
- Création de compte avec envoi automatique des identifiants par email
- Affectation à des entités et directions

###  Gestion Documentaire (GED)
- Arborescence de dossiers avec navigation par fil d'Ariane
- Upload multi-fichiers (50 Mo max, validation MIME)
- Partage de documents (par utilisateur, direction, entité, global, extranet)
- Historique complet des actions (visualisation, téléchargement, partage)
- Stockage privé sécurisé avec contrôle d'accès

###  Formations & E-Learning
- Catalogue de formations avec filtres (catégorie, niveau, durée, mode d'accès)
- Séquences et leçons structurées
- Inscriptions, suivi de progression et évaluations
- Pièces jointes et supports de cours
- Accès extranet pour partenaires externes

### ⏱ Pointage & Badgeuse Virtuelle
- Badgeuse en temps réel (entrée, pause, reprise, sortie)
- Calcul automatique des heures travaillées et heures supplémentaires
- Validation des journées par les managers
- Exports PDF et Excel

###  Gestion des Tâches
- Vue liste et vue Kanban (drag & drop)
- Timer intégré avec suivi du temps passé
- Assignation, priorités, échéances et progression
- Statistiques par espace collaboratif et par collaborateur

###  Agenda & Événements
- Calendrier interactif (FullCalendar)
- Gestion des réunions, formations et événements
- Filtres par visibilité (entité, direction, global)

###  Gestion des Congés
- Demandes de congés avec workflow de validation
- Soldes de congés par type (annuel, maladie, sans solde, formation)
- Calendrier des absences

###  Recrutement
- Publication d'offres d'emploi (avec page publique)
- Pipeline Kanban de candidatures (reçue → examen → entretien → offre → accepté/refusé)
- Planification et notation des entretiens
- Candidatures spontanées et demandes de stage (formulaires publics)

###  Communication Interne
- Actualités et annonces avec ciblage (entité, rôle, direction, global)
- Newsletter avec gestion des abonnés
- Galerie photos

###  Messagerie & Collaboration
- Messagerie interne entre collaborateurs
- Espaces collaboratifs avec membres et discussions
- Notifications en temps réel

###  Analyses & Rapports
- Analyses RH (effectifs, répartition par entité/direction/rôle)
- Rapports de productivité (par utilisateur, par entité, par période)
- Statistiques des tâches (taux de complétion, respect des délais)
- Exports Excel, CSV et PDF

---

##  Stack Technique

| Couche | Technologies |
|--------|-------------|
| **Backend** | Laravel 12, PHP 8.2+, Eloquent ORM |
| **Frontend** | Vue 3, Inertia.js 2, Tailwind CSS 3 |
| **Build** | Vite 7, PostCSS |
| **Base de données** | MySQL 8 / MariaDB 10.6+ |
| **Authentification** | Laravel Breeze, Sanctum |
| **PDF** | Laravel DomPDF |
| **Excel** | Maatwebsite Excel 3 |
| **Calendrier** | FullCalendar 6 |
| **Graphiques** | ApexCharts, Chart.js |
| **Éditeur texte** | TinyMCE 8 |
| **Drag & Drop** | SortableJS |
| **Routes JS** | Ziggy 2 |
| **Alertes** | SweetAlert2 |

---

##  Prérequis

- **PHP** ≥ 8.2 avec extensions : `pdo_mysql`, `mbstring`, `openssl`, `xml`, `ctype`, `fileinfo`, `gd`, `bcmath`
- **Composer** ≥ 2.x
- **Node.js** ≥ 18.x et **npm** ≥ 9.x
- **MySQL** ≥ 8.0 ou **MariaDB** ≥ 10.6
- **Apache** avec `mod_rewrite` activé (ou XAMPP/Laragon)

---

##  Installation

### 1. Cloner le projet

```bash
git clone https://github.com/votre-utilisateur/hrisPlatform.git
cd hrisPlatform
```

### 2. Installer les dépendances

```bash
composer install
npm install
```

### 3. Configurer l'environnement

```bash
cp .env.example .env
php artisan key:generate
```

Éditez `.env` et configurez votre base de données :

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hris_db
DB_USERNAME=root
DB_PASSWORD=votre_mot_de_passe
```

Le fichier `.env.example` fourni à la racine contient toutes les variables nécessaires (base de données, mail SMTP, Reverb WebSockets, Redis, S3). Copiez-le, puis renseignez vos propres identifiants — **ne committez jamais** un `.env` rempli.

---


### 4. Créer la base de données et exécuter les migrations

```bash
# Créer la base de données dans MySQL
mysql -u root -p -e "CREATE DATABASE hris_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Lancer les migrations
php artisan migrate

# (Optionnel) Exécuter les seeders
php artisan db:seed
```

### 5. Créer le lien symbolique storage

```bash
php artisan storage:link
```

### 6. Compiler les assets frontend

```bash
# Développement (avec hot reload)
npm run dev

# Production
npm run build
```

### 7. Lancer le serveur de développement

```bash
php artisan serve
```

L'application sera accessible sur **http://localhost:8000**

> **Mode développement complet** (serveur + queue + logs + vite) :
> ```bash
> composer dev
> ```

---

## Structure du Projet

```
hrisPlatform/
├── app/
│   ├── Console/              # Commandes Artisan
│   ├── Events/               # Événements (notifications temps réel)
│   ├── Http/
│   │   ├── Controllers/      # 41 contrôleurs (32 métier + 9 auth)
│   │   ├── Middleware/        # CheckRole, ScopeEntite, NoCreateForInvite, HandleInertiaRequests
│   │   └── Requests/         # FormRequests (ProfileUpdateRequest, LoginRequest)
│   ├── Models/               # 36 modèles Eloquent
│   ├── Notifications/        # Notifications email
│   └── Providers/            # AppServiceProvider, AuthServiceProvider (Gates)
├── bootstrap/
│   ├── app.php               # Configuration Laravel 12 (middlewares, routes)
│   └── providers.php         # Enregistrement des Service Providers
├── config/                   # 12 fichiers de configuration
├── database/
│   ├── factories/            # Factories pour les tests
│   ├── migrations/           # 67 migrations
│   └── seeders/              # Seeders de données
├── public/
│   ├── build/                # Assets compilés (Vite)
│   ├── .htaccess             # Config Apache
│   └── index.php             # Point d'entrée
├── resources/
│   ├── css/                  # Tailwind + Design System
│   ├── js/
│   │   ├── Components/       # 28+ composants Vue réutilisables
│   │   ├── Composables/      # Composables Vue (useResponsive)
│   │   ├── Layouts/          # AuthenticatedLayout, ExtranetLayout, GuestLayout
│   │   ├── Pages/            # 26 modules de pages Vue
│   │   └── Stores/           # Stores (tacheStore)
│   └── views/                # Vues Blade (app, emails, exports PDF)
├── routes/
│   ├── web.php               # Routes principales
│   ├── auth.php              # Routes d'authentification
│   ├── channels.php          # Canaux de broadcast
│   └── console.php           # Commandes console
├── storage/                  # Fichiers uploadés, logs, cache
├── .env.example              # Template des variables d'environnement
├── .env.production           # Template pour déploiement OVH
├── composer.json             # Dépendances PHP
├── package.json              # Dépendances Node.js
├── vite.config.js            # Configuration Vite
└── tailwind.config.js        # Configuration Tailwind CSS
```

---

## Rôles & Permissions

L'application utilise un système RBAC (Role-Based Access Control) avec 7 rôles hiérarchiques :

| Rôle | Accès | Description |
|------|-------|-------------|
| **Super Admin** |  Global | Accès complet à toutes les entités, gestion des rôles, statistiques publiques, galerie |
| **Admin Entité** |  Entité | Administration de son entité (collaborateurs, documents, formations) |
| **Responsable RH** |  Entité | Gestion RH, analyses, recrutement, congés, pointages |
| **Manager** |  Équipe | Supervision de son équipe, validation des congés et pointages |
| **Formateur** |  Formations | Création et gestion des formations, suivi des inscrits |
| **Collaborateur** |  Personnel | Accès à ses propres données, tâches, documents partagés |
| **Invité (Extranet)** |  Lecture seule | Consultation des documents et formations partagés, sans droit de création |

> Le **Super Admin** dispose d'un bypass global via `Gate::before()` qui lui accorde automatiquement toutes les permissions.

---

##  Configuration Email

L'application envoie des emails pour :
- Identifiants de connexion des nouveaux collaborateurs
- Confirmations de candidature et de demande de stage
- Notifications diverses

### Avec Gmail (développement)

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=votre-email@gmail.com
MAIL_PASSWORD=votre-mot-de-passe-application
MAIL_ENCRYPTION=tls
```

> ⚠️ Utilisez un [mot de passe d'application](https://myaccount.google.com/apppasswords), pas votre mot de passe Gmail.

### Avec OVH (production)

```env
MAIL_MAILER=smtp
MAIL_HOST=ssl0.ovh.net
MAIL_PORT=465
MAIL_USERNAME=contact@votre-domaine.com
MAIL_PASSWORD=votre-mot-de-passe
MAIL_ENCRYPTION=ssl
```

---

## 🌐 Déploiement (OVH Mutualisé)

### Étapes rapides

1. **Compiler les assets** : `npm run build`
2. **Transférer** les fichiers par FTP (exclure `node_modules/`, `.git/`, `tests/`)
3. **Configurer** `.env` sur le serveur avec les identifiants OVH
4. **Exécuter** via SSH :
   ```bash
   php artisan key:generate
   php artisan migrate --force
   php artisan storage:link
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

### Points d'attention

- **DocumentRoot** doit pointer vers `public/` (ou utiliser le `.htaccess` racine fourni)
- **WebSockets** (Reverb) non supportés → configurer `BROADCAST_CONNECTION=null`
- **Queues** → configurer `QUEUE_CONNECTION=sync` ou un Cron OVH
- **Sessions/Cache** → driver `file` recommandé sur mutualisé

> Un script [`deploy_setup.php`](deploy_setup.php) est fourni pour diagnostiquer la configuration post-déploiement.

---

## ⚡ Commandes Utiles

```bash
# Développement
composer dev                    # Serveur + Queue + Logs + Vite en parallèle
php artisan serve               # Serveur PHP uniquement
npm run dev                     # Vite dev server (hot reload)
npm run build                   # Compilation production

# Base de données
php artisan migrate             # Exécuter les migrations
php artisan migrate:rollback    # Annuler la dernière migration
php artisan migrate:fresh       # Recréer toutes les tables
php artisan db:seed             # Exécuter les seeders

# Cache (production)
php artisan config:cache        # Cache la configuration
php artisan route:cache         # Cache les routes
php artisan view:cache          # Cache les vues Blade
php artisan optimize            # Tout en un

# Maintenance
php artisan down                # Mode maintenance
php artisan up                  # Sortir du mode maintenance
php artisan storage:link        # Créer le lien symbolique storage

# Debug
php artisan route:list          # Lister toutes les routes
php artisan tinker              # Console interactive
```

---

##  Contribution

1. **Fork** le projet
2. Créez votre branche : `git checkout -b feature/ma-fonctionnalite`
3. Committez : `git commit -m 'Ajout de ma fonctionnalité'`
4. Pushez : `git push origin feature/ma-fonctionnalite`
5. Ouvrez une **Pull Request**

### Conventions
- Code PHP : **PSR-12** (utiliser `./vendor/bin/pint` pour le formatage)
- Commits : messages en français, préfixés (`feat:`, `fix:`, `docs:`, `refactor:`)
- Vues : composants Vue en **PascalCase**, props en **camelCase**

---

##  Licence

Ce projet est sous licence **MIT**. Voir le fichier [LICENSE](LICENSE) pour plus de détails.

---

