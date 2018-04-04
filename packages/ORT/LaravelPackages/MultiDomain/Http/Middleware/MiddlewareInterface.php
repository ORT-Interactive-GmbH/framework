<?php

namespace ORT\LaravelPackages\MultiDomain\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

interface MiddlewareInterface
{

    public function handle(Request $request, Closure $next, $guard = null): Response;

}
