<?php

namespace ORT\LaravelPackages\Image;

use Route;
use Illuminate\Support\ServiceProvider;
use ORT\LaravelPackages\Image\Services\Image;

class ImageProvider extends ServiceProvider
{

    public function register()
    {
        require_once(__DIR__ . '/Support/helpers.php');
        $this->registerConfig();
        $this->registerServices();
        if ($this->app->runningInConsole()) {
            $this->registerCommands();
        }
    }

    public function boot()
    {
        $this->publishConfig();
        $this->registerRoutes();
        $this->registerMigrations();
        $this->registerTranslations();
        $this->registerViews();
    }

    public function provides()
    {
        return ['image'];
    }

    private function registerConfig()
    {
        $this->mergeConfigFrom(__DIR__ . '/Config/ortimageprovider.php', 'ortimageprovider');
    }

    private function registerServices()
    {
        $this->app->alias('image', Image::class);
    }

    private function registerCommands()
    {
        $this->commands(
            [
                Console\Commands\ThumbnailCreatorCommand::class
            ]
        );
    }

    private function publishConfig()
    {
        $this->publishes([__DIR__ . '/Config/ortimageprovider.php' => config_path('ortimageprovider.php')], 'config');
    }

    private function registerRoutes()
    {
        Route::middleware(['api', 'auth:api'])
            ->namespace('ORT\LaravelPackages\Image\Http\ApiControllers')
            ->prefix('api')
            ->group(__DIR__ . '/Routes/api.php');
        Route::middleware('web')
            ->namespace('ORT\LaravelPackages\Image\Http\Controllers')
            ->group(__DIR__ . '/Routes/web.php');
    }

    private function registerMigrations()
    {
        $this->loadMigrationsFrom(__DIR__ . '/Database/Migrations');
        $this->publishes([__DIR__ . '/Database/Migrations' => database_path('migrations')], 'migrations');
    }

    private function registerTranslations()
    {
        $this->loadTranslationsFrom(__DIR__ . '/Resources/Translations', 'sarahome');
        $this->publishes([__DIR__ . '/Resources/Translations' => resource_path('lang/vendor/sarahome')],
            'translations');
    }

    private function registerViews()
    {
        $this->loadViewsFrom(__DIR__ . '/Resources/Views', 'sarahome');
        $this->publishes([__DIR__ . '/Resources/Views' => resource_path('views/vendor/sarahome')], 'views');
    }

}
