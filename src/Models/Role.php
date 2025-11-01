<?php

namespace Bhhaskin\RolesPermissions\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Role extends Model
{
    use HasFactory;
    protected $guarded = [];

    protected $casts = [
        'name' => 'string',
        'slug' => 'string',
        'description' => 'string',
        'uuid' => 'string',
    ];

    public function getTable(): string
    {
        return config('roles-permissions.tables.roles', parent::getTable());
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            $this->permissionModel(),
            config('roles-permissions.tables.permission_role', 'permission_role')
        )->withTimestamps();
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            $this->userModel(),
            config('roles-permissions.tables.role_user', 'role_user')
        )->withTimestamps();
    }

    protected static function booted(): void
    {
        static::creating(function (self $role) {
            if (! $role->uuid) {
                $role->uuid = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    protected static function newFactory()
    {
        return \Bhhaskin\RolesPermissions\Database\Factories\RoleFactory::new();
    }

    protected function permissionModel(): string
    {
        return config('roles-permissions.models.permission', Permission::class);
    }

    protected function userModel(): string
    {
        return config('roles-permissions.models.user')
            ?? config('auth.providers.users.model')
            ?? 'App\\Models\\User';
    }
}
