<?php

namespace ORT\LaravelPackages\MultiDomain\Models\Traits;

use Illuminate\Database\Eloquent\Builder;
use ORT\LaravelPackages\MultiDomain\Database\Eloquent\MultiDomainScope;

trait MultiDomainModel
{

    public static function bootMultiDomainModel()
    {
        static::addGlobalScope(new MultiDomainScope);
    }

}
