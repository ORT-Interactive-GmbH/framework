<?php

namespace ORT\LaravelPackages\MultiDomain\Models;

class Domain
{

    /**
     * @return string
     */
    public function getDomain(): string
    {
        return config('app.domain');
    }

    /**
     * @return int
     */
    public function getDomainId(): int
    {
        return (int)config('app.domain_id');
    }

    /**
     * @return string
     */
    public function getLocale(): string
    {
        return config('app.locale');
    }

    public function getRouteForDomainId(int $domainId): string
    {
        foreach (config('multidomain.domains') as $domain => $config) {
            if ($config['app']['domain_id'] === $domainId) {
                return $config['app']['url'];
            }
        }
        throw new \RuntimeException('Could not found domain with id: '.$domainId);
    }

}
