<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Roles from the cahier des charges: Administratrice STF, Collaboratrice STF,
     * Mentore, Mentée, Bailleur / partenaire.
     */
    public function run(): void
    {
        $permissions = [
            'users.view', 'users.manage',
            'programs.manage', 'cohorts.manage',
            'matching.manage', 'pairings.manage', 'sessions.manage',
            'groups.manage', 'moderation.manage',
            'cms.manage', 'reports.view', 'reports.manage',
            'audit-logs.view', 'settings.manage', 'newsletter.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'sanctum');
        }

        $admin = Role::findOrCreate('admin', 'sanctum');
        $admin->syncPermissions($permissions);

        $staff = Role::findOrCreate('staff', 'sanctum');
        $staff->syncPermissions([
            'users.view', 'programs.manage', 'cohorts.manage',
            'matching.manage', 'pairings.manage', 'sessions.manage',
            'groups.manage', 'moderation.manage', 'cms.manage',
            'reports.view', 'reports.manage', 'newsletter.manage',
        ]);

        Role::findOrCreate('mentor', 'sanctum');
        Role::findOrCreate('mentee', 'sanctum');
        Role::findOrCreate('donor', 'sanctum');
    }
}
