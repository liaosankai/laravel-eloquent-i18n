<?php

namespace Liaosankai\LaravelEloquentI18n;

use Illuminate\Support\ServiceProvider;

class LaravelEloquentI18nServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            // 讀取套件 migrations
            $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        }

        $this->publishes([
            __DIR__ . '/../database/migrations/2018_06_28_000000_create_translates_table.php' => database_path('migrations/2018_06_28_000000_create_translates_table.php'),
        ], 'laravel-eloquent-i18n');

    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {

    }
}
