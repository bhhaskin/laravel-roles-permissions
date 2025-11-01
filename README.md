# Laravel Roles & Permissions

[![Tests](https://github.com/bhhaskin/laravel-roles-permissions/actions/workflows/tests.yml/badge.svg)](https://github.com/bhhaskin/laravel-roles-permissions/actions/workflows/tests.yml)

Roles and permissions management for Laravel applications.

## Installation

```bash
composer require bhhaskin/laravel-roles-permissions
```

Optionally publish the configuration and migration stubs:

```bash
php artisan vendor:publish --provider="Bhhaskin\RolesPermissions\RolesPermissionsServiceProvider" --tag="laravel-roles-permissions-config"
php artisan vendor:publish --provider="Bhhaskin\RolesPermissions\RolesPermissionsServiceProvider" --tag="laravel-roles-permissions-migrations"
```

Run the migrations:

```bash
php artisan migrate
```

## Setup

Apply the trait to any authenticatable model that should handle roles and permissions:

```php
use Bhhaskin\RolesPermissions\Models\Permission;
use Bhhaskin\RolesPermissions\Models\Role;
use Bhhaskin\RolesPermissions\Traits\HasRoles;

class User extends Authenticatable
{
    use HasRoles;
}
```

By default the package ships with `Role` and `Permission` Eloquent models. You can swap these (and the underlying table names) by publishing the config file.

## Usage

```php
$admin = Role::create(['name' => 'Administrator', 'slug' => 'admin']);
$editor = Role::create(['name' => 'Editor', 'slug' => 'editor']);
$publishPosts = Permission::create(['name' => 'Publish posts', 'slug' => 'publish-posts']);

$user->assignRole($admin);
$user->givePermission($publishPosts);

$user->hasRole('admin'); // true
$user->hasPermission('publish-posts'); // true
$admin->uuid; // UUIDs are generated automatically for frontend consumers
```

Roles automatically inherit all attached permissions, so `hasPermission` will return `true` when a user has a role that grants the ability.

Roles and permissions include a generated `uuid` column, and the default route key is switched to use it so API responses can safely expose the identifier without leaking incremental IDs.

Laravel's class-based factory discovery is supported out of the box; pull in the package and you can call `Role::factory()` or `Permission::factory()` from your tests or seeders. A convenience database seeder (`Bhhaskin\RolesPermissions\Database\Seeders\RolesPermissionsSeeder`) is also provided—run it with `php artisan db:seed --class="Bhhaskin\RolesPermissions\Database\Seeders\RolesPermissionsSeeder"` to quickly populate sample data. If you prefer to customize them, publish the assets:

```bash
php artisan vendor:publish --provider="Bhhaskin\RolesPermissions\RolesPermissionsServiceProvider" --tag="laravel-roles-permissions-factories"
php artisan vendor:publish --provider="Bhhaskin\RolesPermissions\RolesPermissionsServiceProvider" --tag="laravel-roles-permissions-seeders"
```

## Configuration

Publish the config to tweak model classes, table names, or caching preferences. The `cache` section is reserved for future enhancements; for now it stays disabled by default.

Enable object-level assignments by setting `object_permissions.enabled` to `true` in the published config. When enabled you can scope roles and permissions to individual models:

```php
config(['roles-permissions.object_permissions.enabled' => true]);

$editor = Role::create(['name' => 'Project Editor', 'slug' => 'project-editor']);
$approve = Permission::create(['name' => 'Approve Post', 'slug' => 'approve-post']);

$editor->permissions()->attach($approve);

$user->assignRole($editor, $post); // scoped to this $post instance
$user->givePermission($approve, $post); // direct permission for this post

$user->hasRole('project-editor', $post); // true
$user->hasPermission('approve-post', $post); // true
$user->hasPermission('approve-post'); // false, only scoped to the post
```

If you need different role catalogs for different models (for example teams vs organizations) define `role_scopes` in the config:

```php
'role_scopes' => [
    'team' => App\\Models\\Team::class,
    'organization' => App\\Models\\Organization::class,
],
```

Roles created with a matching `scope` value (or created through `Role::factory()->forScope('team')`) can then only be assigned when that context model is supplied, while standalone roles continue to work globally. You can query the catalog by scope using the provided helpers:

```php
Role::forScope('team')->get(); // only team roles
Permission::forScope('organization')->get(); // permissions scoped to organizations
```

### Scoped permission creation

Scoped permissions work the same way as scoped roles. You can generate them via the factories and attach them to matching roles:

```php
$permission = Permission::factory()
    ->forScope('organization')
    ->create([
        'name' => 'View Organization Metrics',
        'slug' => 'view-org-metrics',
    ]);

Role::factory()
    ->forScope('organization')
    ->create(['name' => 'Organization Analyst', 'slug' => 'org-analyst'])
    ->permissions()
    ->attach($permission);
```

To list abilities for a given tenant type you can chain the helpers:

```php
$orgPermissionSlugs = Permission::forScope('organization')->pluck('slug');
```
