<?php

use Bhhaskin\RolesPermissions\Models\Permission;
use Bhhaskin\RolesPermissions\Models\Role;

return [
    'models' => [
        'role' => Role::class,
        'permission' => Permission::class,
        'user' => null,
    ],

    'tables' => [
        'roles' => 'roles',
        'permissions' => 'permissions',
        'role_user' => 'role_user',
        'permission_role' => 'permission_role',
        'permission_user' => 'permission_user',
        'users' => 'users',
    ],

    'cache' => [
        'enabled' => false,
        'store' => null,
        'ttl' => 3600,
    ],

    'object_permissions' => [
        'enabled' => false,
        'columns' => [
            'type' => 'model_type',
            'id' => 'model_id',
        ],
    ],
];
