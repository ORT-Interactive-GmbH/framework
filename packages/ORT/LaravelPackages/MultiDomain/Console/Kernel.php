<?php

namespace ORT\LaravelPackages\MultiDomain\Console;

use App\Console\Kernel as AppConsoleKernel;
use ORT\LaravelPackages\MultiDomain\Console\Application as Artisan;

class Kernel extends AppConsoleKernel
{

    protected function getArtisan()
    {
        if (is_null($this->artisan)) {
            /**
             * wrap the artisan kernel by package kernel
             */
            $artisan = new Artisan($this->app, $this->events, $this->app->version());
            return $this->artisan = $artisan->resolveCommands($this->commands);
        }

        return $this->artisan;
    }

}
