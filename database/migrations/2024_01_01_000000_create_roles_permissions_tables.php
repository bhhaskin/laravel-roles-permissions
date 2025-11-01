<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $tables = config('roles-permissions.tables');

        $rolesTable = $tables['roles'] ?? 'roles';
        $permissionsTable = $tables['permissions'] ?? 'permissions';
        $roleUserTable = $tables['role_user'] ?? 'role_user';
        $permissionRoleTable = $tables['permission_role'] ?? 'permission_role';
        $permissionUserTable = $tables['permission_user'] ?? 'permission_user';
        $usersTable = $tables['users'] ?? 'users';
        $objectPermissions = config('roles-permissions.object_permissions', []);
        $morphConfig = $objectPermissions['columns'] ?? [];
        $morphType = $morphConfig['type'] ?? 'model_type';
        $morphId = $morphConfig['id'] ?? 'model_id';

        if (! Schema::hasTable($rolesTable)) {
            Schema::create($rolesTable, function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->string('name');
                $table->string('slug');
                $table->string('description')->nullable();
                $table->string('scope')->nullable()->index();
                $table->timestamps();
                $table->unique(['slug', 'scope']);
            });
        }

        if (! Schema::hasTable($permissionsTable)) {
            Schema::create($permissionsTable, function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->string('name');
                $table->string('slug');
                $table->string('description')->nullable();
                $table->string('scope')->nullable()->index();
                $table->timestamps();
                $table->unique(['slug', 'scope']);
            });
        }

        if (! Schema::hasTable($roleUserTable)) {
            Schema::create($roleUserTable, function (Blueprint $table) use ($rolesTable, $usersTable, $morphType, $morphId) {
                $table->id();
                $table->foreignId('role_id')->constrained($rolesTable)->cascadeOnDelete();
                $table->foreignId('user_id')->constrained($usersTable)->cascadeOnDelete();
                $table->string($morphType)->nullable();
                $table->unsignedBigInteger($morphId)->nullable();
                $table->timestamps();
                $table->unique(['role_id', 'user_id', $morphType, $morphId], "{$table->getTable()}_unique_assignment");
                $table->index([$morphType, $morphId], "{$table->getTable()}_model_index");
                $table->index('user_id', "{$table->getTable()}_user_index");
                $table->index('role_id', "{$table->getTable()}_role_index");
            });
        }

        if (! Schema::hasTable($permissionRoleTable)) {
            Schema::create($permissionRoleTable, function (Blueprint $table) use ($rolesTable, $permissionsTable) {
                $table->id();
                $table->foreignId('permission_id')->constrained($permissionsTable)->cascadeOnDelete();
                $table->foreignId('role_id')->constrained($rolesTable)->cascadeOnDelete();
                $table->timestamps();
                $table->unique(['permission_id', 'role_id'], "{$table->getTable()}_unique");
                $table->index('permission_id', "{$table->getTable()}_permission_index");
                $table->index('role_id', "{$table->getTable()}_role_index");
            });
        }

        if (! Schema::hasTable($permissionUserTable)) {
            Schema::create($permissionUserTable, function (Blueprint $table) use ($permissionsTable, $usersTable, $morphType, $morphId) {
                $table->id();
                $table->foreignId('permission_id')->constrained($permissionsTable)->cascadeOnDelete();
                $table->foreignId('user_id')->constrained($usersTable)->cascadeOnDelete();
                $table->string($morphType)->nullable();
                $table->unsignedBigInteger($morphId)->nullable();
                $table->timestamps();
                $table->unique(['permission_id', 'user_id', $morphType, $morphId], "{$table->getTable()}_unique_assignment");
                $table->index([$morphType, $morphId], "{$table->getTable()}_model_index");
                $table->index('user_id', "{$table->getTable()}_user_index");
                $table->index('permission_id', "{$table->getTable()}_permission_index");
            });
        }
    }

    public function down(): void
    {
        $tables = config('roles-permissions.tables');

        Schema::dropIfExists($tables['permission_user'] ?? 'permission_user');
        Schema::dropIfExists($tables['permission_role'] ?? 'permission_role');
        Schema::dropIfExists($tables['role_user'] ?? 'role_user');
        Schema::dropIfExists($tables['permissions'] ?? 'permissions');
        Schema::dropIfExists($tables['roles'] ?? 'roles');
    }
};
