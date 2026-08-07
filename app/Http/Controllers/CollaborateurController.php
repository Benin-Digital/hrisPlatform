<?php

namespace App\Http\Controllers;

use App\Models\Entite;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Utilisateur;
use App\Models\Direction;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\BienvenueUtilisateur;
use Illuminate\Validation\Rule;

use Illuminate\Support\Facades\DB;

class CollaborateurController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $entiteId = $request->get('entite_id');
        $directionId = $request->get('direction_id');
        $user = auth()->user();

        $collaborateurs = Utilisateur::query()
            ->with(['direction', 'entite', 'roles'])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nom', 'like', "%{$search}%")
                      ->orWhere('prenom', 'like', "%{$search}%")
                      ->orWhere('matricule', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('poste', 'like', "%{$search}%");
                });
            })
            // Sécurité : Si non super_admin, on force l'entité de l'admin
            ->when(!$user->hasRole('super_admin'), function ($query) use ($user) {
                $query->where('entite_id', $user->entite_id);
            }, function ($query) use ($entiteId) {
                // Si super_admin, on permet le filtrage optionnel par entite_id
                $query->when($entiteId, fn($q) => $q->where('entite_id', $entiteId));
            })
            // Filtre par direction
            ->when($directionId, fn($q) => $q->where('direction_id', $directionId))
            ->orderBy('nom')
            ->orderBy('prenom')
            ->paginate(15)
            ->withQueryString();

        // Récupération des entités pour le filtre
        $entites = \App\Models\Entite::select('id', 'nom')
            ->when(!$user->hasRole('super_admin'), function ($q) use ($user) {
                $q->where('id', $user->entite_id);
            })
            ->orderBy('nom')->get();

        // Récupération des directions pour le filtre
        $directions = \App\Models\Direction::select('id', 'nom', 'entite_id')
            ->when(!$user->hasRole('super_admin'), function ($q) use ($user) {
                $q->where('entite_id', $user->entite_id);
            })
            ->when($user->hasRole('super_admin') && $entiteId, function ($q) use ($entiteId) {
                $q->where('entite_id', $entiteId);
            })
            ->orderBy('nom')->get();

        return Inertia::render('Collaborateurs/Index', [
            'collaborateurs' => $collaborateurs,
            'entites' => $entites,
            'directions' => $directions,
            'filters' => [
                'search' => $search,
                'entite_id' => $entiteId,
                'direction_id' => $directionId,
            ],
        ]);
    }

    public function create()
    {
        $currentUser = auth()->user();
        $entites = Entite::select('id', 'nom')
            ->when(!$currentUser->hasRole('super_admin'), function ($query) use ($currentUser) {
                $query->where('id', $currentUser->entite_id);
            })
            ->orderBy('nom')->get();

        $roles = Role::select('id', 'nom', 'nom_affichage')
                       ->orderBy('niveau')
                       ->get();

        $permissions = Permission::all()->groupBy('categorie');

        $directions = Direction::select('id', 'nom')
            ->when(!$currentUser->hasRole('super_admin'), function ($query) use ($currentUser) {
                $query->where('entite_id', $currentUser->entite_id);
            })
            ->orderBy('nom')->get();

        return Inertia::render('Collaborateurs/Create', [
            'entites' => $entites,
            'directions' => $directions,
            'roles'   => $roles,
            'availablePermissions' => $permissions,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'prenom'                => 'required|string|max:100',
            'nom'                   => 'required|string|max:100',
            'email'                 => 'required|email|unique:utilisateurs,email',
            'matricule'             => 'nullable|string|max:50|unique:utilisateurs,matricule',
            'poste'                 => 'nullable|string|max:150',
            'direction_id'          => 'nullable|exists:directions,id',
            'entite_id'             => [
                'nullable',
                'exists:entites,id',
                Rule::requiredIf(fn () => $request->input('type') === 'interne'),
            ],
            'role_ids'              => 'required|array|min:1',
            'role_ids.*'            => [
                'exists:roles,id',
                function ($attribute, $value, $fail) use ($request) {
                    $role = Role::find($value);
                    if (!$role) return;

                    if ($request->input('type') === 'externe' && $role->nom !== 'invite') {
                        $fail("Le rôle '{$role->nom_affichage}' n'est pas autorisé pour un utilisateur externe.");
                    }

                    if ($request->input('type') === 'interne' && $role->nom === 'invite') {
                        $fail("Le rôle 'invite' n'est pas autorisé pour un utilisateur interne.");
                    }
                },
            ],

            'permission_ids'   => 'nullable|array',
            'permission_ids.*' => 'exists:permissions,id',
            'type'                  => 'required|in:interne,externe',
            'date_embauche'         => 'nullable|date',
            'statut'                => 'required|in:actif,inactif,suspendu,conges',
            'dashboard_preference'  => 'nullable|string',
        ]);

        // Forcer entite_id = null si externe
        if ($validated['type'] === 'externe') {
            $validated['entite_id'] = null;
            $validated['direction_id'] = null;
        }

        // Génération automatique du mot de passe
        $plainPassword = Str::random(10);
        $passwordSource = 'généré automatiquement et envoyé par email';

        $validated['mot_de_passe'] = Hash::make($plainPassword);

        // Valeurs par défaut
        $validated['langue']         = 'fr';
        $validated['fuseau_horaire'] = 'Europe/Paris';
        $validated['theme']          = 'clair';
        $validated['type_contrat']   = 'CDI';

        // Forcer l'entité si l'admin n'est pas super_admin
        $admin = auth()->user();
        if (!$admin->hasRole('super_admin')) {
            $validated['entite_id'] = $admin->entite_id;
        }

        $maxRetries = 3;
        $attempts = 0;
        $user = null;

        while ($attempts < $maxRetries && !$user) {
            $attempts++;
            
            // Matricule auto generation inside the loop to get a fresh one on retry
            if (empty($request->input('matricule'))) {
                $prefix = $validated['type'] === 'interne' ? 'INT' : 'EXT';
                // Use (no soft delete) to find next ID
                $nextId = Utilisateur::max('id') + 1 + ($attempts - 1); // Increment on retry
                
                do {
                    $matricule = $prefix . str_pad($nextId, 6, '0', STR_PAD_LEFT);
                    $exists = DB::table('utilisateurs')->where('matricule', $matricule)->exists();
                    if ($exists) {
                        $nextId++;
                    }
                } while ($exists);
                
                $validated['matricule'] = $matricule;
            }

            try {
                DB::beginTransaction();

                // Création utilisateur
                $user = Utilisateur::create($validated);

                // Attribution multi-rôles avec pivot
                $pivotData = [];
                foreach ($validated['role_ids'] as $roleId) {
                    $pivotData[$roleId] = [
                        'assigne_le'  => now(),
                        'assigne_par' => auth()->id(),
                    ];
                }
                $user->roles()->attach($pivotData);

                // Permissions directes
                if ($request->has('permission_ids')) {
                    $permPivot = [];
                    foreach ($request->input('permission_ids', []) as $pId) {
                        $permPivot[$pId] = [
                            'assigne_le'  => now(),
                            'assigne_par' => auth()->id(),
                        ];
                    }
                    $user->permissions()->attach($permPivot);
                }

                DB::commit();

            } catch (\Illuminate\Database\QueryException $e) {
                DB::rollBack();
                // Check for duplicate entry error specifically
                if ($e->getCode() == 23000 && str_contains($e->getMessage(), 'utilisateurs_matricule_unique')) {
                    if (!empty($request->input('matricule'))) {
                        // If matricule was provided by user, we can't retry with a new one
                        throw $e;
                    }
                    // Continue to next iteration to generate a new matricule
                    $user = null;
                    continue;
                }
                throw $e; // Throw other errors
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        }

        if (!$user) {
            \Log::error('Échec création utilisateur après retries', $validated);
            return back()->with('error', 'Erreur lors de la création de l’utilisateur (conflit de matricule), veuillez réessayer.');
        }

        // Email de bienvenue
        try {
            Mail::to($user->email)->send(new BienvenueUtilisateur($user, $plainPassword));
            $emailStatus = 'Email envoyé avec succès.';
        } catch (\Exception $e) {
            \Log::warning("Échec envoi email bienvenue à {$user->email} : " . $e->getMessage());
            $emailStatus = 'Attention : l’email n’a pas pu être envoyé.';
        }

        return redirect()
            ->route('collaborateurs.index')
            ->with('success', "Utilisateur créé avec succès ! Mot de passe : {$passwordSource}. {$emailStatus}");
    }

    public function edit($id)
    {
        $collaborateur = Utilisateur::with('direction', 'entite', 'roles')->findOrFail($id);
        $currentUser = auth()->user();

        // Protection cross-entité
        if (!$currentUser->hasRole('super_admin') && $collaborateur->entite_id !== $currentUser->entite_id) {
            abort(403, "Vous ne pouvez pas modifier un collaborateur d'une autre entité.");
        }

        $entites = Entite::select('id', 'nom')
            ->when(!$currentUser->hasRole('super_admin'), function ($query) use ($currentUser) {
                $query->where('id', $currentUser->entite_id);
            })
            ->orderBy('nom')->get();
            
        $roles   = Role::select('id', 'nom', 'nom_affichage')->orderBy('niveau')->get();
        $permissions = Permission::all()->groupBy('categorie');
        $userPermissions = $collaborateur->permissions->pluck('id');

        $directions = Direction::select('id', 'nom', 'entite_id')
            ->when(!$currentUser->hasRole('super_admin'), function ($query) use ($currentUser) {
                $query->where('entite_id', $currentUser->entite_id);
            })
            ->orderBy('nom')->get();

        return Inertia::render('Collaborateurs/Edit', [
            'collaborateur' => $collaborateur,
            'entites'       => $entites,
            'directions'    => $directions,
            'roles'         => $roles,
            'availablePermissions' => $permissions,
            'userPermissions' => $userPermissions,
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = Utilisateur::findOrFail($id);

        // Protection cross-entité
        $admin = auth()->user();
        if (!$admin->hasRole('super_admin') && $user->entite_id !== $admin->entite_id) {
            abort(403, "Vous ne pouvez pas modifier un collaborateur d'une autre entité.");
        }

        $validated = $request->validate([
            'prenom'        => 'required|string|max:100',
            'nom'           => 'required|string|max:100',
            'matricule'     => 'nullable|string|max:50|unique:utilisateurs,matricule,' . $id,
            'email'         => 'required|email|unique:utilisateurs,email,' . $id,
            'poste'         => 'nullable|string|max:150',
            'direction_id'  => 'nullable|exists:directions,id',
            'date_embauche' => 'nullable|date',
            'statut'        => 'required|in:actif,inactif,suspendu,conges',
            'type'          => 'required|in:interne,externe',
            'entite_id'     => [
                'nullable',
                'exists:entites,id',
                Rule::requiredIf(fn () => $request->input('type') === 'interne'),
            ],
            'role_ids'      => 'required|array|min:1',
            'role_ids.*'    => [
                'exists:roles,id',
                function ($attribute, $value, $fail) use ($request) {
                    $role = Role::find($value);
                    if (!$role) return;

                    if ($request->input('type') === 'externe' && $role->nom !== 'invite') {
                        $fail("Le rôle '{$role->nom_affichage}' n'est pas autorisé pour un utilisateur externe.");
                    }

                    if ($request->input('type') === 'interne' && $role->nom === 'invite') {
                        $fail("Le rôle 'invite' n'est pas autorisé pour un utilisateur interne.");
                    }
                },
            ],
            'permission_ids'   => 'nullable|array',
            'permission_ids.*' => 'exists:permissions,id',
            'dashboard_preference' => 'nullable|string',
            'password'=> 'nullable|string|min:8|confirmed',
        ]);
        if ($request->filled('password')) {
            $validated['mot_de_passe'] = Hash::make($request->password);
        }

        // Forcer l'entité si l'admin n'est pas super_admin
        $admin = auth()->user();
        if (!$admin->hasRole('super_admin')) {
            $validated['entite_id'] = $admin->entite_id;
        }

        $user->update($validated);

        // Sync rôles
        $pivotData = [];
        foreach ($validated['role_ids'] as $roleId) {
            $pivotData[$roleId] = [
                'assigne_le'  => now(),
                'assigne_par' => auth()->id(),
            ];
        }
        $user->roles()->sync($pivotData);

        // Sync permissions directes
        $permPivot = [];
        if ($request->has('permission_ids')) {
            foreach ($request->input('permission_ids', []) as $pId) {
                $permPivot[$pId] = [
                    'assigne_le'  => now(),
                    'assigne_par' => auth()->id(),
                ];
            }
        }
        $user->permissions()->sync($permPivot);

        return redirect()
            ->route('collaborateurs.index')
            ->with('success', 'Collaborateur mis à jour avec succès.');
    }

    public function show($id)
    {
        $collaborateur = Utilisateur::with([
            'entite', 
            'direction', 
            'roles',
            'formations' => function($q) {
                $q->orderBy('created_at', 'desc');
            },
            'taches' => function($q) {
                $q->orderBy('created_at', 'desc')->limit(20);
            },
            'documentsProprietaire' => function($q) {
                $q->orderBy('created_at', 'desc')->limit(20);
            },
            'historiqueDocuments' => function($q) {
                $q->orderBy('created_at', 'desc')->limit(20);
            },

            ])->findOrFail($id);

        $currentUser = auth()->user();

        if (!$currentUser->hasRole('super_admin') && $collaborateur->entite_id !== $currentUser->entite_id) {
            abort(403);
        }

        //Construire l'historique des activités 
        $historique = [];

    // Documents partagés/téléchargés
    foreach ($collaborateur->historiqueDocuments as $log) {
        $historique[] = [
            'date' => $log->created_at,
            'type' => 'document',
            'description' => $log->action . ' : ' . ($log->details ?? 'Document'),
        ];
    }

    // Tâches terminées
    foreach ($collaborateur->taches->where('statut', 'terminee') as $tache) {
        $historique[] = [
            'date' => $tache->updated_at ?? $tache->created_at,
            'type' => 'tache',
            'description' => 'A terminé la tâche : ' . $tache->titre,
        ];
    }

    // Inscriptions aux formations
    foreach ($collaborateur->formations as $formation) {
        $historique[] = [
            'date' => $formation->pivot->created_at,
            'type' => 'formation',
            'description' => 'S\'est inscrit à la formation : ' . $formation->titre,
        ];
    }

    // Trier par date décroissante
    usort($historique, function($a, $b) {
        return $b['date'] <=> $a['date'];
    });

    $historique = array_slice($historique, 0, 30);
    

        return Inertia::render('Collaborateurs/Show', [
            'collaborateur' => $collaborateur,
            'historique' => $historique,
            'canEdit'=> $currentUser->hasRole('super_admin') || $currentUser->hasRole('admin_entite') || $currentUser->hasRole('responsable_rh'),
        ]);
    }

    public function destroy($id)
    {
        $collaborateur = Utilisateur::findOrFail($id);
        $admin = auth()->user();

        if (!$admin->hasRole('super_admin') && $collaborateur->entite_id !== $admin->entite_id) {
            abort(403);
        }

        $collaborateur->delete();

        return redirect()
            ->route('collaborateurs.index')
            ->with('success', 'Utilisateur supprimé avec succès.');
    }
}