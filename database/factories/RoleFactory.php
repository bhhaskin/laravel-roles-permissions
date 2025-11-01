<?php

namespace Bhhaskin\RolesPermissions\Database\Factories;

use Bhhaskin\RolesPermissions\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Role>
 */
class RoleFactory extends Factory
{
    protected $model = Role::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->jobTitle();

        return [
            'name' => $name,
            'slug' => Str::slug($name . '-' . Str::random(5)),
            'description' => $this->faker->optional()->sentence(),
            'scope' => null,
        ];
    }

    public function forScope(?string $scope): self
    {
        return $this->state(fn () => ['scope' => $scope]);
    }
}
