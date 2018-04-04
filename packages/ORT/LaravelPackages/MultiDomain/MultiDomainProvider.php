<?php

namespace ORT\LaravelPackages\MultiDomain;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Routing\UrlGenerator as BaseUrlGenerator;
use Symfony\Component\Console\Input\ArgvInput;
use ORT\LaravelPackages\MultiDomain\Http\Middleware\LocaleSelectionMiddleware;
use ORT\LaravelPackages\MultiDomain\Models\Domain;
use ORT\LaravelPackages\MultiDomain\Services\Routing\UrlGenerator;

class MultiDomainProvider extends ServiceProvider
{

    /**
     *
     */
    public function register()
    {
        require_once(__DIR__ . '/Support/helpers.php');

        $domain = null;
        if ($this->app->runningInConsole()) {
            $domain = (new ArgvInput())->getParameterOption('--domain');
        } else {
            $domain = app('request')->header('Host');
        }
        $this->registerConfig($domain);
        $this->app->alias('domain', Domain::class);
        $this->app->instance('domain', new Domain());
    }

    /**
     * @param string $domain
     */
    private function registerConfig(string &$domain)
    {
        $this->mergeConfigFrom(__DIR__ . '/Config/multidomain.php', 'multidomain');
        $path = config_path('multidomain.php');
        if (file_exists($path)) {
            $this->mergeConfigFrom($path, 'multidomain');
        }
        $path = config_path('multidomain.local.php');
        if (file_exists($path)) {
            $this->mergeConfigFrom($path, 'multidomain');
        }

        $config = config();
        $multiConfig = $config->get('multidomain');
        $domain = isset($multiConfig['domains'][$domain]) ? $domain : $multiConfig['default'];
        $configDomain = $multiConfig['domains'][$domain];

        $configAll = $config->all();
        $configAll = $this->mergeConfig($configAll, $configDomain);
        $configAll = $this->mergeConfig($configAll, ['app' => ['domain' => $domain]]);
        $config->set($configAll);

        if (isset($multiConfig['domains'][$domain]['env'])) {
            foreach ($multiConfig['domains'][$domain]['env'] as $key => $value) {
                putenv(sprintf('%s=%s', $key, $value));
                $_ENV[$key] = $value;
            }
        }
    }

    /**
     * @param array $config1
     * @param array $config2
     * @return array
     */
    private function mergeConfig(array &$config1, array $config2): array
    {
        foreach ($config2 as $key => $value) {
            $config1[$key] = isset($config1[$key]) && is_array($config1[$key])
                ? $this->mergeConfig($config1[$key], $config2[$key])
                : $config2[$key];
        }
        return $config1;
    }

    /**
     *
     */
    public function boot(\Illuminate\Contracts\Http\Kernel $kernel)
    {
        $kernel->pushMiddleware(LocaleSelectionMiddleware::class);

        $this->publishes(
            [
                __DIR__ . '/Config/multidomain.php' => config_path('multidomain.php')
            ],
            'config'
        );
        $this->app['view']->composer('*', function (View $view) {
            $domain = app('domain');
            $view->with(compact('domain'));
        });

        $this->app['url'] = $this->app[BaseUrlGenerator::class] = (new UrlGenerator())->setInner($this->app['url']);
    }
}
