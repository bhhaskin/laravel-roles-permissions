<?php

namespace Bhhaskin\RolesPermissions\Database\Factories;

use Bhhaskin\RolesPermissions\Models\Permission;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Permission>
 */
class PermissionFactory extends Factory
{
    protected $model = Permission::class;

    public function definition(): array
    {
        $verb = $this->faker->randomElement(['view', 'edit', 'delete', 'approve', 'publish']);
        $object = $this->faker->randomElement(['posts', 'users', 'reports', 'comments']);
        $name = Str::title("{$verb} {$object}");

        return [
            'name' => $name,
            'slug' => Str::slug($name . '-' . Str::random(4)),
            'description' => $this->faker->optional()->sentence(),
        ];
    }
}
