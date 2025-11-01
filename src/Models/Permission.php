<?php

namespace Bhhaskin\RolesPermissions\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Permission extends Model
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
        return config('roles-permissions.tables.permissions', parent::getTable());
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            $this->roleModel(),
            config('roles-permissions.tables.permission_role', 'permission_role')
        )->withTimestamps();
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            $this->userModel(),
            config('roles-permissions.tables.permission_user', 'permission_user')
        )->withTimestamps();
    }

    protected static function booted(): void
    {
        static::creating(function (self $permission) {
            if (! $permission->uuid) {
                $permission->uuid = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    protected static function newFactory()
    {
        return \Bhhaskin\RolesPermissions\Database\Factories\PermissionFactory::new();
    }

    protected function roleModel(): string
    {
        return config('roles-permissions.models.role', Role::class);
    }

    protected function userModel(): string
    {
        return config('roles-permissions.models.user')
            ?? config('auth.providers.users.model')
            ?? 'App\\Models\\User';
    }
}
