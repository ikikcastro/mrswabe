<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $owner = Role::firstOrCreate(['name' => 'owner']);
        Role::firstOrCreate(['name' => 'customer']);

        $ownerPermissions = ['manage-rooms', 'manage-reservations', 'view-reports'];
        foreach ($ownerPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $owner->syncPermissions($ownerPermissions);
    }
}
