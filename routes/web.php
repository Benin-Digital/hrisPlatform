<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CollaborateurController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\AnnonceController;
use App\Http\Controllers\EvenementController;
use App\Http\Controllers\FormationController;
use App\Http\Controllers\EntiteController;
use App\Http\Controllers\TacheController;
use App\Http\Controllers\ProductiviteController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\EspaceCollaboratifController;
use App\Http\Controllers\AnalyseTacheController;
use App\Http\Controllers\AnalyseRhController;
use App\Http\Controllers\CongeController;
use App\Http\Controllers\CalendrierController;
use App\Http\Controllers\RecrutementController;
use App\Http\Controllers\PointageController;
use App\Http\Controllers\OffreEmploiController; // ✅ Ajouté
//use App\Http\Controllers\CandidatureController;  // ✅ Ajouté
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\RubriqueController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\StatistiquePubliqueController;
use App\Http\Controllers\CandidatureController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// ============================================================
// PAGE D'ACCUEIL PUBLIQUE
// ============================================================
Route::get('/', function () {
    try {
        $latestAnnonces = \App\Models\Annonce::where('visibilite', 'global')
            ->latest()
            ->take(3)
            ->get();

        $galleryImages = \App\Models\Gallery::where('is_visible', true)
            ->orderBy('order')
            ->get();

        $statistiquesPubliques = \App\Models\StatistiquePublique::where('is_published', true)
            ->orderBy('ordre')
            ->get();

        $offresPubliques = \App\Models\OffreEmploi::where('is_published', true)
            ->where(function($query) {
                $query->whereNull('date_expiration')
                      ->orWhere('date_expiration', '>=', now());
            })
            ->latest()
            ->get();

        // ✅ Récupération des stages
        $stages = \App\Models\OffreEmploi::where('is_published', true)
            ->where('type_contrat', 'Stage')
            ->where(function($query) {
                $query->whereNull('date_expiration')
                      ->orWhere('date_expiration', '>=', now());
            })
            ->latest()
            ->take(4)
            ->get();

        $formationsPubliques = \App\Models\Formation::where('statut', 'publie')
            ->where('est_public', true)
            ->latest()
            ->take(6)
            ->get();

        return Inertia::render('Welcome', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'laravelVersion' => Application::VERSION,
            'phpVersion' => PHP_VERSION,
            'annonces' => $latestAnnonces,
            'gallery' => $galleryImages,
            'statistiquesPubliques' => $statistiquesPubliques,
            'offres' => $offresPubliques,
            'stages' => $stages,
            'formations' => $formationsPubliques,
        ]);
    } catch (\Exception $e) {
        \Log::error('Erreur sur la page d\'accueil : ' . $e->getMessage());
        return Inertia::render('Error', ['message' => 'Une erreur est survenue.']);
    }
});

// ============================================================
// ROUTES PUBLIQUES (sans authentification)
// ============================================================
Route::post('/newsletter/subscribe', [NewsletterController::class, 'subscribe'])->name('newsletter.subscribe');

// Candidature publique (hors auth)
Route::post('/offres/{offre}/postuler', [CandidatureController::class, 'store'])->name('candidatures.store');

// Offres publiques (consultation)
Route::get('/offres', [OffreEmploiController::class, 'publicIndex'])->name('offres.public.index');
Route::get('/offres/{offre}', [OffreEmploiController::class, 'publicShow'])->name('offres.public.show');
// Demande de stage (sans offre)
Route::get('/stage/demande', [App\Http\Controllers\StageController::class, 'create'])->name('stage.demande');
Route::post('/stage/demande', [App\Http\Controllers\StageController::class, 'store'])->name('stage.demande.store');


// ============================================================
// TOUTES LES ROUTES AUTHENTIFIÉES
// ============================================================
Route::middleware('auth')->group(function () {

    // Dashboard redirection selon rôle
    Route::get('/dashboard', function () {
        $role = auth()->user()->mainRole()?->nom;

        $dashboardRoute = match ($role) {
            'super_admin'    => 'super-admin.dashboard',
            'admin_entite'   => 'admin-entite.dashboard',
            'responsable_rh' => 'rh.dashboard',
            'manager'        => 'manager.dashboard',
            'formateur'      => 'formateur.dashboard',
            'collaborateur'  => 'collaborateur.dashboard',
            'invite'         => 'extranet.dashboard',
            default          => 'collaborateur.dashboard',
        };

        return redirect()->route($dashboardRoute);
    })->name('dashboard');

    // ============================================================
    // SUPER ADMIN
    // ============================================================
    Route::middleware('role:super_admin')
        ->prefix('super-admin')
        ->name('super-admin.')
        ->group(function () {
            Route::get('/', [SuperAdminController::class, 'dashboard'])->name('dashboard');

            Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
            Route::get('/roles/create', [RoleController::class, 'create'])->name('roles.create');
            Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
            Route::get('/roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit');
            Route::put('/roles/{role}', [RoleController::class, 'update'])->name('roles.update');
            Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');

            Route::resource('entites', EntiteController::class);
            Route::resource('gallery', GalleryController::class);

            Route::get('/newsletters', [NewsletterController::class, 'index'])->name('newsletters.index');
            Route::delete('/newsletters/{newsletter}', [NewsletterController::class, 'destroy'])->name('newsletters.destroy');

            Route::resource('statistiques-publiques', StatistiquePubliqueController::class);
            Route::post('/statistiques-publiques/{id}/toggle', [StatistiquePubliqueController::class, 'togglePublish'])->name('statistiques-publiques.toggle');
            Route::post('/statistiques-publiques-generate', [StatistiquePubliqueController::class, 'generateFromProductivite'])->name('statistiques-publiques.generate');
            Route::post('/statistiques-publiques-reorder', [StatistiquePubliqueController::class, 'reorder'])->name('statistiques-publiques.reorder');
        });

    // ============================================================
    // GESTION DES OFFRES (Super Admin & RH)
    // ============================================================
    Route::middleware('role:super_admin,responsable_rh')
        ->prefix('super-admin')
        ->name('super-admin.')
        ->group(function () {
            Route::resource('offres', OffreEmploiController::class);
            Route::post('/offres/{offre}/toggle-publish', [OffreEmploiController::class, 'togglePublish'])->name('offres.toggle-publish');

            Route::get('/candidatures', [CandidatureController::class, 'index'])->name('candidatures.index');
            Route::get('/candidatures/{candidature}/download-cv', [CandidatureController::class, 'downloadCv'])->name('candidatures.download-cv');
            Route::delete('/candidatures/{candidature}', [CandidatureController::class, 'destroy'])->name('candidatures.destroy');
        });

    // ============================================================
    // ADMIN ENTITÉ
    // ============================================================
    Route::middleware('role:admin_entite,super_admin')
        ->prefix('admin-entite')
        ->name('admin-entite.')
        ->group(function () {
            Route::get('/', [DashboardController::class, 'adminEntite'])->name('dashboard');
        });

    // ============================================================
    // RESPONSABLE RH
    // ============================================================
    Route::middleware('role:responsable_rh,admin_entite,super_admin')
        ->prefix('rh')
        ->name('rh.')
        ->group(function () {
            Route::get('/', [DashboardController::class, 'rh'])->name('dashboard');
        });

    // ============================================================
    // ANALYSES RH (avec exports)
    // ============================================================
    Route::get('/analyses', [AnalyseRhController::class, 'index'])
        ->middleware('role:responsable_rh,admin_entite,super_admin')
        ->name('rh.analyses');

    Route::get('/analyses/export-excel', [AnalyseRhController::class, 'exportExcel'])
        ->middleware('role:responsable_rh,admin_entite,super_admin')
        ->name('rh.analyses.export-excel');

    Route::get('/analyses/export-csv', [AnalyseRhController::class, 'exportCsv'])
        ->middleware('role:responsable_rh,admin_entite,super_admin')
        ->name('rh.analyses.export-csv');

    Route::get('/analyses/export-pdf', [AnalyseRhController::class, 'exportPdf'])
        ->middleware('role:responsable_rh,admin_entite,super_admin')
        ->name('rh.analyses.export-pdf');

    // ============================================================
    // MANAGER
    // ============================================================
    Route::middleware('role:manager,responsable_rh,admin_entite,super_admin')
        ->prefix('manager')
        ->name('manager.')
        ->group(function () {
            Route::get('/', [DashboardController::class, 'manager'])->name('dashboard');
        });

    // ============================================================
    // FORMATEUR
    // ============================================================
    Route::middleware('role:formateur,manager,responsable_rh,admin_entite,super_admin')
        ->prefix('formateur')
        ->name('formateur.')
        ->group(function () {
            Route::get('/', [DashboardController::class, 'formateur'])->name('dashboard');
        });

    // ============================================================
    // COLLABORATEUR
    // ============================================================
    Route::middleware('role:collaborateur,formateur,manager,responsable_rh,admin_entite,super_admin')
        ->prefix('collaborateur')
        ->name('collaborateur.')
        ->group(function () {
            Route::get('/', [DashboardController::class, 'collaborateur'])->name('dashboard');
        });

    // ============================================================
    // EXTRANET (invités)
    // ============================================================
    Route::middleware('role:invite')
        ->prefix('extranet')
        ->name('extranet.')
        ->group(function () {
            Route::get('/', [DashboardController::class, 'extranet'])->name('dashboard');
        });

    // ============================================================
    // PROFIL
    // ============================================================
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ============================================================
    // DOCUMENTS
    // ============================================================
    Route::prefix('documents')->name('documents.')->group(function () {
        Route::get('/view/{uuid}', [DocumentController::class, 'view'])->name('view');
        Route::get('/download/{uuid}', [DocumentController::class, 'download'])->name('download');

        Route::middleware('no.create.invite')->group(function () {
            Route::delete('/{uuid}', [DocumentController::class, 'destroy'])->name('destroy');
            Route::post('/dossiers', [DocumentController::class, 'storeDossier'])->name('storeDossier');
            Route::post('/upload/{dossier_id?}', [DocumentController::class, 'upload'])
                ->where('dossier_id', '(root|\d+)')
                ->name('upload');
            Route::post('/partager/{uuid}', [DocumentController::class, 'partager'])->name('partager');
        });

        Route::get('/{dossier?}', [DocumentController::class, 'index'])
            ->where('dossier', '.*')
            ->name('index');
    });

    // ============================================================
    // FORMATIONS
    // ============================================================
    Route::prefix('formations')->name('formations.')->group(function () {
        Route::get('/', [FormationController::class, 'index'])->name('index');
        Route::get('/{id}', [FormationController::class, 'show'])->where('id', '[0-9]+')->name('show');
        Route::post('/{id}/evaluation', [FormationController::class, 'storeEvaluation'])->name('evaluation');

        Route::middleware('no.create.invite')->group(function () {
            Route::get('/create', [FormationController::class, 'create'])->name('create');
            Route::post('/', [FormationController::class, 'store'])->name('store');
            Route::get('/{id}/edit', [FormationController::class, 'edit'])->where('id', '[0-9]+')->name('edit');
            Route::put('/{id}', [FormationController::class, 'update'])->where('id', '[0-9]+')->name('update');
            Route::patch('/{id}', [FormationController::class, 'update'])->where('id', '[0-9]+');
            Route::delete('/{id}', [FormationController::class, 'destroy'])->where('id', '[0-9]+')->name('destroy');
            Route::get('/{id}/download/{fileName}', [FormationController::class, 'downloadDocument'])->name('download');
            Route::post('/{id}/inscrire', [FormationController::class, 'inscrire'])->name('inscrire');
        });
    });

    // ============================================================
    // AGENDA / ÉVÉNEMENTS
    // ============================================================
    Route::prefix('agenda')->name('agenda.')->group(function () {
        Route::get('/', [EvenementController::class, 'index'])->name('index');
        Route::get('/{id}', [EvenementController::class, 'show'])->where('id', '[0-9]+')->name('show');

        Route::middleware('no.create.invite')->group(function () {
            Route::get('/create', [EvenementController::class, 'create'])->name('create');
            Route::post('/', [EvenementController::class, 'store'])->name('store');
            Route::get('/{id}/edit', [EvenementController::class, 'edit'])->where('id', '[0-9]+')->name('edit');
            Route::put('/{id}', [EvenementController::class, 'update'])->where('id', '[0-9]+')->name('update');
            Route::delete('/{id}', [EvenementController::class, 'destroy'])->where('id', '[0-9]+')->name('destroy');
        });
    });

    // ============================================================
    // ACTUALITÉS / ANNONCES
    // ============================================================
    Route::prefix('Actualites')->name('actualites.')->group(function () {
        Route::get('/', [AnnonceController::class, 'index'])->name('index');
        Route::get('/{id}', [AnnonceController::class, 'show'])->where('id', '[0-9]+')->name('show');

        Route::middleware('no.create.invite')->group(function () {
            Route::get('/create', [AnnonceController::class, 'create'])->name('create');
            Route::post('/', [AnnonceController::class, 'store'])->name('store');
            Route::get('/{id}/edit', [AnnonceController::class, 'edit'])->name('edit');
            Route::put('/{id}', [AnnonceController::class, 'update'])->name('update');
            Route::patch('/{id}', [AnnonceController::class, 'update']);
            Route::delete('/{id}', [AnnonceController::class, 'destroy'])->name('destroy');
        });
    });

    // ============================================================
    // MESSAGERIE
    // ============================================================
    Route::prefix('messages')->name('messages.')->group(function () {
        Route::get('/contacts', [MessageController::class, 'contacts'])->name('contacts');
        Route::get('/{id}', [MessageController::class, 'index'])->name('index');
        Route::post('/', [MessageController::class, 'store'])->name('store');
    });

    // ============================================================
    // ESPACES COLLABORATIFS
    // ============================================================
    Route::prefix('collaboration')->name('collaboration.')->group(function () {
        Route::get('/', [EspaceCollaboratifController::class, 'index'])->name('index');
        Route::get('/creer', [EspaceCollaboratifController::class, 'create'])->name('create');
        Route::post('/', [EspaceCollaboratifController::class, 'store'])->name('store');
        Route::get('/{uuid}', [EspaceCollaboratifController::class, 'show'])->name('show');
        Route::post('/{uuid}/inviter', [EspaceCollaboratifController::class, 'addMember'])->name('add-member');
    });

    // ============================================================
    // GALERIE
    // ============================================================
    Route::get('/gallery', [GalleryController::class, 'index'])->name('gallery.index');

    // ============================================================
    // RUBRIQUES
    // ============================================================
    Route::middleware(['role:super_admin,admin_entite,responsable_rh,manager'])
        ->prefix('rubriques')
        ->name('rubriques.')
        ->group(function () {
            Route::get('/', [RubriqueController::class, 'index'])->name('index');
            Route::post('/', [RubriqueController::class, 'store'])->name('store');
            Route::put('/{rubrique}', [RubriqueController::class, 'update'])->name('update');
            Route::delete('/{rubrique}', [RubriqueController::class, 'destroy'])->name('destroy');
        });

    // ============================================================
    // GESTION DES COLLABORATEURS
    // ============================================================
    Route::middleware(['role:super_admin,admin_entite,responsable_rh'])
        ->prefix('collaborateurs')
        ->name('collaborateurs.')
        ->group(function () {
            Route::get('/', [CollaborateurController::class, 'index'])->name('index');
            Route::get('/create', [CollaborateurController::class, 'create'])->name('create');
            Route::post('/', [CollaborateurController::class, 'store'])->name('store');
            Route::get('/{id}', [CollaborateurController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [CollaborateurController::class, 'edit'])->name('edit');
            Route::patch('/{id}', [CollaborateurController::class, 'update'])->name('update');
            Route::delete('/{id}', [CollaborateurController::class, 'destroy'])->name('destroy');
        });

    // ============================================================
    // CONGÉS
    // ============================================================
    Route::prefix('conges')->name('conges.')->middleware(['auth'])->group(function () {
        Route::get('/', [CongeController::class, 'index'])->name('index');
        Route::get('/create', [CongeController::class, 'create'])->name('create');
        Route::post('/', [CongeController::class, 'store'])->name('store');
        Route::get('/{id}', [CongeController::class, 'show'])->name('show');
        Route::patch('/{id}/valider', [CongeController::class, 'valider'])->name('valider');
        Route::patch('/{id}/annuler', [CongeController::class, 'annuler'])->name('annuler');
    });

    // ============================================================
    // PRODUCTIVITÉ (tous)
    // ============================================================
    Route::prefix('productivite')->name('productivite.')->middleware(['auth'])->group(function () {
        Route::get('/', [ProductiviteController::class, 'index'])->name('index');
        Route::get('/utilisateur/{id?}', [ProductiviteController::class, 'parUtilisateur'])->name('utilisateur');
        Route::get('/entite/{id?}', [ProductiviteController::class, 'parEntite'])->name('entite');
        Route::get('/rapport', [ProductiviteController::class, 'rapportPeriode'])->name('rapport');
    });

    // ============================================================
    // TÂCHES (tous)
    // ============================================================
    Route::prefix('taches')->name('taches.')->middleware(['auth'])->group(function () {
        Route::get('/', [TacheController::class, 'index'])->name('index');
        Route::get('/create', [TacheController::class, 'create'])->name('create');
        Route::post('/', [TacheController::class, 'store'])->name('store');
        Route::get('/{id}', [TacheController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [TacheController::class, 'edit'])->name('edit');
        Route::put('/{id}', [TacheController::class, 'update'])->name('update');
        Route::delete('/{id}', [TacheController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/progress', [TacheController::class, 'updateProgress'])->name('update-progress');
        Route::get('/kanban', [TacheController::class, 'kanban'])->name('kanban');
        Route::get('/download/{tacheId}/{nomStocke}', [TacheController::class, 'downloadFile'])->name('download');
        Route::get('/stats/espace/{id}', [AnalyseTacheController::class, 'getEspaceStats'])->name('stats.espace');
        Route::get('/stats/collaborateur/{id}', [AnalyseTacheController::class, 'getCollaborateurStats'])->name('stats.collaborateur');
        Route::post('/{id}/timer/start', [TacheController::class, 'startTimer'])->name('taches.timer.start');
        Route::post('/{id}/timer/stop', [TacheController::class, 'stopTimer'])->name('taches.timer.stop');
        Route::patch('/{id}/temps', [TacheController::class, 'updateTemps'])->name('taches.update-temps');
        Route::patch('/{id}/statut', [TacheController::class, 'updateStatus'])->name('taches.update-status');
        Route::get('/{id}/historique-temps', [TacheController::class, 'getHistorique'])->name('get-historique-temps');
    });

    // ============================================================
    // NOTIFICATIONS
    // ============================================================
    Route::prefix('notifications')->name('notifications.')->middleware(['auth'])->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::patch('/{id}/read', [NotificationController::class, 'markAsRead'])->name('mark-read');
        Route::patch('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
    });

    // ============================================================
    // CALENDRIER
    // ============================================================
    Route::get('/calendrier/evenements', [CalendrierController::class, 'getEvenements'])
        ->middleware('auth')
        ->name('calendrier.evenements');

    // ============================================================
    // POINTAGE
    // ============================================================
    Route::prefix('pointages')->name('pointages.')->middleware(['auth'])->group(function () {
        Route::get('/', [PointageController::class, 'index'])->name('index');
        Route::post('/badgeuse', [PointageController::class, 'badgeuse'])->name('badgeuse');
        Route::get('/stats/{userId?}', [PointageController::class, 'stats'])->name('stats');
        Route::get('/export-pdf', [PointageController::class, 'exportPDF'])->name('export-pdf');
        Route::get('/export-excel', [PointageController::class, 'exportExcel'])->name('export-excel');
        Route::patch('/{id}/valider', [PointageController::class, 'validerJournee'])->name('valider');
        Route::get('/{id}', [PointageController::class, 'show'])->name('show');

    });
    
    

    // ============================================================
    // TEST BROADCAST (à supprimer après validation)
    // ============================================================
    Route::get('/test-broadcast/{userId}', function ($userId) {
        $user = \App\Models\Utilisateur::find($userId);
        if (!$user) {
            return response()->json(['error' => 'Utilisateur non trouvé'], 404);
        }

        $notification = (object) [
            'id' => uniqid(),
            'data' => [
                'message' => '🔔 Ceci est un test de notification en temps réel !',
                'titre' => 'Test WebSocket',
            ],
            'created_at' => now(),
        ];

        broadcast(new \App\Events\NouvelleNotification($notification, $userId));

        return response()->json([
            'success' => true,
            'message' => 'Broadcast envoyé à l\'utilisateur ' . $userId
        ]);
    });
    Route::get('/recrutement', function () {
        return redirect()->route('recrutement.pipeline');
    })->name('recrutement.index');

    // ============================================================
    // RECRUTEMENT (Pipeline)
    // ============================================================
    Route::prefix('recrutement')->name('recrutement.')->middleware(['auth', 'role:super_admin,responsable_rh,manager'])->group(function () {
        Route::get('/pipeline', [RecrutementController::class, 'pipeline'])->name('pipeline');
        Route::patch('/{id}/statut', [RecrutementController::class, 'changeStatut'])->name('change-statut');
        Route::post('/{id}/planifier-entretien', [RecrutementController::class, 'planifierEntretien'])->name('planifier-entretien');
        Route::post('/{id}/noter-entretien', [RecrutementController::class, 'noterEntretien'])->name('noter-entretien');
        Route::post('/{id}/valider', [RecrutementController::class, 'valider'])->name('valider');
        Route::post('/{id}/rejeter', [RecrutementController::class, 'rejeter'])->name('rejeter');
        //Route::get('/candidatures/{candidature}/download-cv', [CandidatureController::class, 'downloadCv'])->name('recrutement.download-cv');
        Route::get('/candidatures/{candidature}/download-cv', [CandidatureController::class, 'downloadCv'])->name('download-cv');
        Route::get('/{id}/planifier-entretien', [RecrutementController::class, 'showPlanifierEntretien'])->name('planifier-form');
        Route::get('/candidature/spontanee', [CandidatureController::class, 'createSpontanee'])->name('candidatures.spontanee.create');
        Route::post('/candidature/spontanee', [CandidatureController::class, 'storeSpontanee'])->name('candidatures.spontanee.store');
        
    });
});

// ============================================================
// AUTH (fichier séparé)
// ============================================================
require __DIR__.'/auth.php';