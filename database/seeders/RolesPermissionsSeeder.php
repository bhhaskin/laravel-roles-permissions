<?php

namespace Bhhaskin\RolesPermissions\Database\Seeders;

use Bhhaskin\RolesPermissions\Models\Permission;
use Bhhaskin\RolesPermissions\Models\Role;
use Illuminate\Database\Seeder;

class RolesPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = Permission::factory()->count(5)->create();

        Role::factory()
            ->count(3)
            ->create()
            ->each(function (Role $role) use ($permissions) {
                $role->permissions()->attach($permissions->random(rand(1, $permissions->count())));
            });
    }
}
