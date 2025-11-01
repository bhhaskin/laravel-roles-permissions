<?php

namespace Bhhaskin\RolesPermissions\Database\Seeders;

use Bhhaskin\RolesPermissions\Models\Permission;
use Bhhaskin\RolesPermissions\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RolesPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = Permission::factory()->count(5)->create();

        $roles = Role::factory()->count(3)->create();

        foreach ((array) config('roles-permissions.role_scopes', []) as $scope => $class) {
            if (is_int($scope)) {
                $scope = Str::snake(class_basename($class));
            }

            $roles->push(
                Role::factory()
                    ->forScope($scope)
                    ->create([
                        'name' => Str::title($scope . ' role'),
                        'slug' => Str::slug($scope . '-role-' . Str::random(4)),
                    ])
            );
        }

        $roles->each(function (Role $role) use ($permissions) {
            $role->permissions()->attach($permissions->random(rand(1, $permissions->count())));
        });
    }
}
