<?php

namespace Core;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    /**
     * {@inheritDoc}
     */
    public function registerBundles()
    {
        // This app doesn't rely on and doesn't use bundles.
        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $configDir = $this->getConfigDir();

        $loader->load($configDir.'/{packages}/*.yaml', 'glob');
        $loader->load($configDir.'/{packages}/'.$this->environment.'/**/*.yaml', 'glob');
    }

    /**
     * Returns the path to the directory where the app configuration is kept.
     *
     * @return string
     */
    private function getConfigDir(): string
    {
        return $this->getProjectDir().'/config';
    }
}
