<?php

use Bhhaskin\RolesPermissions\Models\Permission;
use Bhhaskin\RolesPermissions\Models\Role;
use Bhhaskin\RolesPermissions\Tests\Fixtures\Post;
use Bhhaskin\RolesPermissions\Tests\Fixtures\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

it('assigns, checks, and removes roles', function () {
    $user = User::create([
        'name' => 'Role User',
        'email' => sprintf('role-user-%s@example.com', Str::uuid()),
        'password' => bcrypt('password'),
    ]);

    $admin = Role::create(['name' => 'Administrator', 'slug' => 'admin']);
    $moderator = Role::create(['name' => 'Moderator', 'slug' => 'moderator']);

    expect(Str::isUuid($admin->uuid))->toBeTrue();
    expect(Str::isUuid($moderator->uuid))->toBeTrue();

    $user->assignRole($admin, $moderator);
    $user->refresh();

    expect($user->roles)->toHaveCount(2);
    expect($user->hasRole('admin'))->toBeTrue();
    expect($user->hasAnyRole('admin', 'editor'))->toBeTrue();
    expect($user->hasAllRoles('admin', 'moderator'))->toBeTrue();

    $user->removeRole($moderator);
    $user->refresh();

    expect($user->hasRole('moderator'))->toBeFalse();
    expect($user->roles)->toHaveCount(1);
});

it('handles direct and inherited permissions', function () {
    $user = User::create([
        'name' => 'Permission User',
        'email' => sprintf('permission-user-%s@example.com', Str::uuid()),
        'password' => bcrypt('password'),
    ]);

    $role = Role::create(['name' => 'Editor', 'slug' => 'editor']);
    $publish = Permission::create(['name' => 'Publish Posts', 'slug' => 'publish-posts']);
    $feature = Permission::create(['name' => 'Feature Posts', 'slug' => 'feature-posts']);

    expect(Str::isUuid($publish->uuid))->toBeTrue();
    expect(Str::isUuid($feature->uuid))->toBeTrue();

    $role->permissions()->attach($publish);
    $user->assignRole($role);
    $user->givePermission($feature);
    $user->refresh();

    expect($user->hasPermission('publish-posts'))->toBeTrue(); // via role
    expect($user->hasPermission('feature-posts'))->toBeTrue(); // direct

    $user->revokePermission($feature);
    $user->refresh();

    expect($user->hasPermission('feature-posts'))->toBeFalse();
});

it('supports object level roles and permissions when enabled', function () {
    config()->set('roles-permissions.object_permissions.enabled', true);

    $user = User::create([
        'name' => 'Scoped User',
        'email' => sprintf('scoped-user-%s@example.com', Str::uuid()),
        'password' => bcrypt('password'),
    ]);

    $post = Post::create(['title' => 'Scoped Post']);
    $otherPost = Post::create(['title' => 'Another Post']);

    $editor = Role::create(['name' => 'Project Editor', 'slug' => 'project-editor']);
    $approve = Permission::create(['name' => 'Approve Post', 'slug' => 'approve-post']);
    $feature = Permission::create(['name' => 'Feature Scoped Post', 'slug' => 'feature-scoped-post']);

    $editor->permissions()->attach($approve);

    $user->assignRole($editor, $post);
    $user->givePermission($feature, $post);
    $user->refresh();

    expect($user->hasRole('project-editor'))->toBeFalse();
    expect($user->hasRole('project-editor', $post))->toBeTrue();
    expect($user->hasRole('project-editor', $otherPost))->toBeFalse();

    expect($user->hasPermission('approve-post'))->toBeFalse();
    expect($user->hasPermission('approve-post', $post))->toBeTrue();
    expect($user->hasPermission('approve-post', $otherPost))->toBeFalse();

    expect($user->hasDirectPermission('feature-scoped-post'))->toBeFalse();
    expect($user->hasDirectPermission('feature-scoped-post', $post))->toBeTrue();
    expect($user->hasDirectPermission('feature-scoped-post', $otherPost))->toBeFalse();

    expect(Gate::forUser($user)->allows('feature-scoped-post', $post))->toBeTrue();
    expect(Gate::forUser($user)->allows('feature-scoped-post', $otherPost))->toBeFalse();

    $user->revokePermission($feature, $post);
    $user->refresh();

    expect($user->hasPermission('feature-scoped-post', $post))->toBeFalse();

    $user->removeRole($editor, $post);
    $user->refresh();

    expect($user->hasRole('project-editor', $post))->toBeFalse();
});
