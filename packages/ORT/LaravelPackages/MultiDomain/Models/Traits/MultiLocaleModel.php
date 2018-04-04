<?php

namespace ORT\LaravelPackages\MultiDomain\Models\Traits;

use Illuminate\Database\Eloquent\Builder;
use ORT\LaravelPackages\MultiDomain\Database\Eloquent\MultiLocaleScope;

trait MultiLocaleModel
{

    public static function bootMultiLocaleModel()
    {
        static::addGlobalScope(new MultiLocaleScope);
    }

}
