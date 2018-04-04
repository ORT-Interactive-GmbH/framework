<?php

namespace ORT\LaravelPackages\MultiDomain\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response as LaravelResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpFoundation\Cookie;

class LocaleSelectionMiddleware implements MiddlewareInterface
{

    public function handle(Request $request, Closure $next, $guard = null): SymfonyResponse
    {
        $domain = app('domain');
        if ($request->getHost() !== $domain->getDomain()) {
            return redirect('//' . $domain->getDomain());
        }
        $whiteList = config('app.locales');
        $url = parse_url($request->getUri());
        $locale = null;
        if (isset($url['path'])) {
            $regex = sprintf('(/(%s)(.*))', implode('|', $whiteList));
            if (preg_match($regex, $url['path'], $match)) {
                $locale = $match[1];
                $url['path'] = $match[2];
            }
        }

        if (!$locale) {
            $url = sprintf(
                '/%s/%s%s%s',
                config()->get('app.locale'),
                ltrim($url['path'], '/'),
                isset($url['query']) && !empty($url['query']) ? '?' : '',
                $url['query'] ?? ''
            );
            return redirect()->to(url($url));
        }

        $locale = $locale ?? $request->get('locale', null); // URL
        $locale = in_array($locale, $whiteList) ? $locale : null;
        $locale = $locale ?? $request->cookie('locale', null); // Cookie
        $locale = in_array($locale, $whiteList) ? $locale : null;
        $locale = $locale ?? $request->getDefaultLocale(); // Browser
        $locale = in_array($locale, $whiteList) ? $locale : null;
        $locale = $locale ?? config()->get('app.locale'); // Config
        $locale = in_array($locale, $whiteList) ? $locale : config('app.fallback_locale'); // Fallback

        app()->setLocale($locale);
        if ($locale == 'de_DE') {
            Carbon::setLocale('de_DE');
            setlocale(LC_ALL, 'de_DE.UTF8', 'de_DE', 'de', 'ge');
        } elseif ($locale == 'de_CH') {
            Carbon::setLocale('de_CH');
            @setlocale(LC_ALL, 'de_CH.UTF8', 'de_CH', 'de', 'ge');
        } elseif ($locale == 'de_AT') {
            Carbon::setLocale('de_AT');
            @setlocale(LC_ALL, 'de_AT.UTF8', 'de_AT', 'de', 'ge');
        } elseif ($locale == 'fr_CH') {
            // @TODO FR
        }

        /**
         * very dirty long hack ;-)
         * if you create a new instance from Request the session token will be lost,
         * but ... at this time the session isn't loaded ... so .... WTF ....
         */
        if (isset($url['path'])) {
            $rc = new \ReflectionClass($request);
            $property = $rc->getProperty('pathInfo');
            $property->setAccessible(true);
            $property->setValue($request, $url['path']);
        }

        /** @var LaravelResponse $response */
        $response = $next($request);
        if (method_exists($response,'withCookie')) {
            $response->withCookie(new Cookie('locale', $locale));
        }
        return $response;
    }

}
