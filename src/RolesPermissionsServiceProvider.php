<?php

namespace Bhhaskin\RolesPermissions;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class RolesPermissionsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/roles-permissions.php', 'roles-permissions');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/roles-permissions.php' => $this->configPath('roles-permissions.php'),
            ], 'laravel-roles-permissions-config');

            $this->publishes([
                __DIR__ . '/../database/migrations/' => database_path('migrations'),
            ], 'laravel-roles-permissions-migrations');
        }

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        $this->registerGateBeforeCallback();
    }

    protected function registerGateBeforeCallback(): void
    {
        Gate::before(function ($user, string $ability, array $arguments = []) {
            if (! method_exists($user, 'hasPermission')) {
                return null;
            }

            $target = $arguments[0] ?? null;

            if ($target instanceof EloquentModel && $user->hasPermission($ability, $target)) {
                return true;
            }

            if ($user->hasPermission($ability)) {
                return true;
            }

            return null;
        });
    }

    protected function configPath(string $path): string
    {
        if (function_exists('config_path')) {
            return config_path($path);
        }

        return $this->app->basePath('config/' . $path);
    }
}
