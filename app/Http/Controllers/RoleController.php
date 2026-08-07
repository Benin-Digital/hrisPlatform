<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::withCount('utilisateurs')->orderBy('niveau')->get();

        return Inertia::render('SuperAdmin/Roles/Index', [
            'roles' => $roles,
        ]);
    }

    public function create()
    {
        $permissions = Permission::all()->groupBy('categorie');

        return Inertia::render('SuperAdmin/Roles/Create', [
            'permissions' => $permissions,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required|unique:roles,nom',
            'nom_affichage' => 'required',
            'description' => 'nullable',
            'niveau' => 'required|integer|unique:roles,niveau',
            'permissions' => 'array',
        ]);

        $role = Role::create($request->only(['nom', 'nom_affichage', 'description', 'niveau', 'est_systeme' => false]));

        if ($request->permissions) {
            foreach ($request->permissions as $permissionId => $accorde) {
                $role->permissions()->attach($permissionId, ['accorde' => $accorde]);
            }
        }

        return redirect()->route('super-admin.roles.index')->with('success', 'Rôle créé avec succès');
    }

    public function edit(Role $role)
    {
        $role->load('permissions');
        $permissions = Permission::all()->groupBy('categorie');

        $rolePermissions = $role->permissions->pluck('pivot.accorde', 'id')->toArray();

        return Inertia::render('SuperAdmin/Roles/Edit', [
            'role' => $role,
            'permissions' => $permissions,
            'rolePermissions' => $rolePermissions,
        ]);
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'nom' => 'required|unique:roles,nom,' . $role->id,
            'nom_affichage' => 'required',
            'description' => 'nullable',
            'niveau' => 'required|integer|unique:roles,niveau,' . $role->id,
            'permissions' => 'array',
        ]);

        $role->update($request->only(['nom', 'nom_affichage', 'description', 'niveau']));

        // Sync permissions
        $syncData = [];
        if ($request->permissions) {
            foreach ($request->permissions as $permissionId => $accorde) {
                $syncData[$permissionId] = ['accorde' => $accorde];
            }
        }
        $role->permissions()->sync($syncData);

        return redirect()->route('super-admin.roles.index')->with('success', 'Rôle mis à jour');
    }

    public function destroy(Role $role)
    {
        if ($role->est_systeme) {
            return back()->with('error', 'Impossible de supprimer un rôle système');
        }

        $role->delete();

        return back()->with('success', 'Rôle supprimé');
    }
}