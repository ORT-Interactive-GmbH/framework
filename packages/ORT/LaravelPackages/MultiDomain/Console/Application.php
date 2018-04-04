<?php

namespace ORT\LaravelPackages\MultiDomain\Console;

use Symfony\Component\Console\Input\InputOption;
use Illuminate\Console\Application as BaseApplication;

class Application extends BaseApplication
{

    /**
     * Get the default input definitions for the applications.
     *
     * This is used to add the --domain option to every available command.
     *
     * @return \Symfony\Component\Console\Input\InputDefinition
     */
    protected function getDefaultInputDefinition()
    {
        return tap(parent::getDefaultInputDefinition(), function ($definition) {
            $definition->addOption($this->getDomainOption());
            $definition->addOption($this->getLocaleOption());
        });
    }

    /**
     * Get the global domain option for the definition.
     *
     * @return \Symfony\Component\Console\Input\InputOption
     */
    protected function getDomainOption()
    {
        $message = 'The domain the command should run under.';
        return new InputOption('--domain', null, InputOption::VALUE_OPTIONAL, $message);
    }

    /**
     * Get the global locale option for the definition.
     *
     * @return \Symfony\Component\Console\Input\InputOption
     */
    protected function getLocaleOption()
    {
        $message = 'The locale the command should run under.';
        return new InputOption('--locale', null, InputOption::VALUE_OPTIONAL, $message);
    }

}
