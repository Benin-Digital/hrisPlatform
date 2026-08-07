<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;
use App\Models\RolePermission;

class InterfaceRoleSeeder extends Seeder
{
    public function run(): void
    {
        // Rôle Admin
        $admin = Role::where('nom', 'super_admin')->first();
        if ($admin) {
            $permissions = Permission::whereIn('nom', ['access_internet', 'access_extranet', 'access_intranet', 'create_profiles', 'manage_externes'])->get();
            foreach ($permissions as $perm) {
                RolePermission::updateOrCreate([
                    'role_id' => $admin->id,
                    'permission_id' => $perm->id,
                ], ['accorde' => true]);
            }
        }

        // Rôle RH
        $rh = Role::where('nom', 'responsable_rh')->first();
        if ($rh) {
            $permissions = Permission::whereIn('nom', ['access_intranet', 'access_extranet', 'manage_externes'])->get();
            foreach ($permissions as $perm) {
                RolePermission::updateOrCreate([
                    'role_id' => $rh->id,
                    'permission_id' => $perm->id,
                ], ['accorde' => true]);
            }
        }

        // Rôle Collaborateur (interne)
        $collaborateur = Role::where('nom', 'collaborateur')->first();
        if ($collaborateur) {
            RolePermission::updateOrCreate([
                'role_id' => $collaborateur->id,
                'permission_id' => Permission::where('nom', 'access_intranet')->first()->id,
            ], ['accorde' => true]);
        }

        // Rôle Invite/Client (externe)
        $invite = Role::where('nom', 'invite')->first();
        if ($invite) {
            RolePermission::updateOrCreate([
                'role_id' => $invite->id,
                'permission_id' => Permission::where('nom', 'access_extranet')->first()->id,
            ], ['accorde' => true]);
        }
    }
};