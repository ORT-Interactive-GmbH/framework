<?php

namespace ORT\LaravelPackages\MultiDomain\Services\Routing;

use Illuminate\Contracts\Routing\UrlGenerator as InterfaceUrlGenerator;

class UrlGenerator implements InterfaceUrlGenerator
{

    /** @var InterfaceUrlGenerator */
    private $inner;

    /**
     * @param InterfaceUrlGenerator $inner
     * @return UrlGenerator
     */
    public function setInner(InterfaceUrlGenerator $inner): self
    {
        $this->inner = $inner;
        return $this;
    }

    /**
     * @param string $name
     * @param array $parameters
     * @return mixed
     */
    public function __call(string $name, array $parameters = [])
    {
        $result = call_user_func_array([$this->inner, $name], $parameters);

        return $result;
    }

    /**
     * Get the current URL for the request.
     *
     * @return string
     */
    public function current()
    {
        $result = $this->inner->current();

        return $result;
    }

    /**
     * Generate an absolute URL to the given path.
     *
     * @param  string $path
     * @param  mixed $extra
     * @param  bool $secure
     * @return string
     */
    public function to($path, $extra = [], $secure = null)
    {
        $result = $this->inner->to($path, $extra, $secure);

        return $result;
    }

    /**
     * Generate a secure, absolute URL to the given path.
     *
     * @param  string $path
     * @param  array $parameters
     * @return string
     */
    public function secure($path, $parameters = [])
    {
        $result = $this->inner->secure($path, $parameters);

        return $result;
    }

    /**
     * Generate the URL to an application asset.
     *
     * @param  string $path
     * @param  bool $secure
     * @return string
     */
    public function asset($path, $secure = null)
    {
        $result = $this->inner->asset($path, $secure);
        /** @TODO using cdn? * */
        /*$url = parse_url($result);
        $url['host'] = 'cdn.canon-academy-laravel.app';
        $result = build_url($url);*/
        return $result;
    }

    /**
     * Get the URL to a named route.
     *
     * @param  string $name
     * @param  mixed $parameters
     * @param  bool $absolute
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    public function route($name, $parameters = [], $absolute = true)
    {
        $locale = app('domain')->getLocale();
        if (isset($parameters['locale'])) {
            $locale = $parameters['locale'];
            unset($parameters['locale']);
        }
        $result = $this->inner->route($name, $parameters, $absolute);
        $url = parse_url($result);
        $url['path'] = sprintf(
            '/%s%s',
            $locale,
            $url['path'] ?? ''
        );
        $result = build_url($url);
        return $result;
    }

    /**
     * Get the URL to a controller action.
     *
     * @param  string $action
     * @param  mixed $parameters
     * @param  bool $absolute
     * @return string
     */
    public function action($action, $parameters = [], $absolute = true)
    {
        $result = $this->inner->action($action, $parameters, $absolute);

        return $result;
    }

    /**
     * Set the root controller namespace.
     *
     * @param  string $rootNamespace
     * @return InterfaceUrlGenerator
     */
    public function setRootControllerNamespace($rootNamespace)
    {
        $result = $this->inner->setRootControllerNamespace($rootNamespace);

        return $result;
    }

}
