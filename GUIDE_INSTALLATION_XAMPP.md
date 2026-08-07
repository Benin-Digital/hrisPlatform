# Guide de Déploiement et de Reproduction du Projet (XAMPP / MySQL)

Ce guide détaille pas à pas comment reproduire ce projet (basé sur **Laravel 12**, **Vue 3**, **Inertia.js**, **Reverb** et **Tailwind CSS**) sur une nouvelle machine équipée de **XAMPP**.

---

## 🛠️ 1. Les Technologies et leur Utilité

Voici la liste des technologies utilisées dans ce projet et leur rôle :

*   **PHP (>= 8.2)** : Le langage principal du backend (fourni par XAMPP).
*   **MySQL (via XAMPP)** : Le système de gestion de base de données. Il stockera toutes les informations dynamiques.
*   **Apache (via XAMPP)** : Serveur web. (On privilégiera cependant `php artisan serve` pour le développement local).
*   **Composer** : Le gestionnaire de dépendances pour PHP. Il télécharge toutes les librairies Laravel requises listées dans `composer.json`.
*   **Node.js & NPM** : L'environnement et le gestionnaire de paquets JavaScript pour compiler les composants frontend (Vue 3, Tailwind, via Vite).
*   **Laravel 12** : Le framework backend PHP robuste qui gère la logique de l'application, l'API interne, et la sécurité.
*   **Inertia.js & Vue 3** : Le duo permettant d'avoir une application "Single Page" (SPA) sans construire une API séparée.
*   **Vite** : L'outil de compilation ultra-rapide du frontend.
*   **Laravel Reverb** : Serveur WebSocket intégré pour gérer les événements en temps réel (notifications, chat).

---

## ⚙️ 2. Les Prérequis à Installer sur la Nouvelle Machine

Avant de copier le projet, installez ces éléments essentiels :

1.  **XAMPP** : Téléchargez XAMPP avec **PHP 8.2 (ou supérieur)** ([apachefriends.org](https://www.apachefriends.org/fr/index.html)).
2.  **Composer** : L'installeur global pour PHP ([getcomposer.org](https://getcomposer.org/download/)).
3.  **Node.js** : Téléchargez la version LTS ([nodejs.org](https://nodejs.org/)).
4.  **Git** (Optionnel mais recommandé) : Pour cloner et utiliser un terminal performant (`Git Bash`) sous Windows.

> **Pour vérifier l'installation**, ouvrez votre terminal (CMD, PowerShell ou Git Bash) et tapez :
> *   `php -v`
> *   `composer -v`
> *   `node -v`
> *   `npm -v`

---

## 🚀 3. Reproduction et Installation (Étape par Étape)

### Étape 1 : Copier ou Cloner le projet
Prenez l'intégralité du dossier du projet et placez-le dans le répertoire de travail. Si vous utilisez XAMPP, vous pouvez le mettre dans le dossier par défaut, par exemple : `C:\xampp\htdocs\hrisPlatform`.

### Étape 2 : Ouvrir un Terminal
Ouvrez votre terminal et placez-vous dans le dossier du projet :
```bash
cd c:\xampp\htdocs\hrisPlatform
```

### Étape 3 : Installer les Dépendances Backend (PHP)
Le dossier `vendor` n'étant jamais copié, nous devons dire à Composer de réinstaller le cœur de Laravel et ses librairies.
```bash
composer install
```

### Étape 4 : Le fichier de Configuration Environnementale (`.env`)
Ce fichier contient les mots de passe de votre BDD et des clés sensibles. Il faut le créer à partir de l'exemple.
1. Dupliquez le fichier :
```bash
copy .env.example .env
```
*(Sur Git Bash ou MacOS/Linux : `cp .env.example .env`)*

2. Générer la clé d'application cryptographique unique :
```bash
php artisan key:generate
```

### Étape 5 : Créer la Base de Données (XAMPP -> MySQL)
1. Ouvrez le **Panneau de contrôle XAMPP** et démarrez **Apache** et **MySQL**.
2. Allez sur votre navigateur : `http://localhost/phpmyadmin`
3. Créez une nouvelle base de données en utf8mb4 (ex: `hrisplatform_db`).

### Étape 6 : Lier la BDD au Projet
Ouvrez le fichier `.env` fraîchement créé dans l'éditeur de texte et configurez les accès à MySQL :

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hrisplatform_db  # Mettre le nom de votre Base de Données
DB_USERNAME=root             # root par défaut sur XAMPP
DB_PASSWORD=                 # Laisser vide par défaut sur XAMPP
```

### Étape 7 : Créer les Tables (Migrations)
Pour injecter la structure de la base de données (tables) :
```bash
php artisan migrate
```
*(Optionnel) Si le projet contient des données par défaut à charger, utilisez : `php artisan migrate --seed`*

### Étape 8 : Lier les Fichiers de Stockage
Pour gérer les images et documents publics stockés :
```bash
php artisan storage:link
```

### Étape 9 : Installer les Dépendances Frontend (Node.js)
Il faut installer Vue, Tailwind, et Vite (le dossier `node_modules`).
```bash
npm install
```

### Étape 10 : Compiler les Assets Vue/Tailwind
```bash
npm run build
```
*(Ou `npm run dev` pour travailler en direct dessus).*

---

## 🏃 4. Démarrer et Accéder au Projet en Local

### La meilleure méthode de développement (Artisan Serve)
Depuis votre terminal dans le projet, lancez :
```bash
php artisan serve
```
Le serveur va démarrer et vous pourrez accéder à l'application via : **`http://127.0.0.1:8000`**

### La commande magique du projet (Tout en un)
J'ai remarqué dans le fichier `composer.json` une commande dev sur-mesure hyper pratique pour ce projet. Au lieu de lancer plusieurs terminaux manuellement, exécutez simplement :
```bash
npm run dev
```
Cette commande lance simultanément le serveur PHP (`serve`), la file d'attente (`queue`), les logs et le build Vue en temps réel !

---

## 🔔 5. Services en Arrière-plan (Temps réel et Tâches)
Puisque le projet utilise `laravel/reverb` (WebSockets) et des Queues, si jamais certaines fonctionnalités asynchrones (notifications temps réel, envoi de mails) ne fonctionnent pas, assurez-vous de démarrer aussi ces services.

Si vous n'utilisez pas le "tout en un" (`npm run dev`), ouvrez **d'autres terminaux** pour ces commandes :

1.  **Démarrer les Websockets Reverb** (Pour le chat et temps réel) :
    ```bash
    php artisan reverb:start
    ```
2.  **Démarrer la file d'attente/Workers** (Pour de gros processus ou des emails) :
    ```bash
    php artisan queue:listen
    ```
