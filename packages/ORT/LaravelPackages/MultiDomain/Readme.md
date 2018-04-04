# ORT Laravel MultiDomain
Dieses Package erlaubt es Domain abhängig Konfiguration zu
überschreiben.

## Setup
bootstrap/app.php
```php
$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    #App\Console\Kernel::class
    ORT\LaravelPackages\MultiDomain\Console\Kernel::class
);
```
vs.
```php
$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    ORT\LaravelPackages\MultiDomain\Console\Kernel::class
);
```

## Quellen
- https://github.com/thinksaydo/envtenant
- https://github.com/andrewjwolf/laravel-multidomain
- https://github.com/gecche/laravel-multidomain
- https://laracasts.com/discuss/channels/general-discussion/running-artisan-commands-in-a-multi-tenancy-laraveldoctrine-app
