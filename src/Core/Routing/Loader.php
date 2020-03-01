<?php

declare(strict_types=1);

namespace App\Core\Routing;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouteCollectionBuilder;

class Loader
{
    /**
     * @var string
     */
    private string $configDir;

    /**
     * @var string
     */
    private string $environment;

    /**
     * @param string $configDir
     * @param string $environment
     */
    public function __construct(string $configDir, string $environment)
    {
        $this->configDir = $configDir;
        $this->environment = $environment;
    }

    /**
     * @param LoaderInterface $loader
     *
     * @return RouteCollection
     */
    public function load(LoaderInterface $loader): RouteCollection
    {
        $routes = new RouteCollectionBuilder($loader);

        $routes->import($this->configDir.'/{routes}/*.yaml', '/', 'glob');
        $routes->import($this->configDir.'/{routes}/'.$this->environment.'/**/*.yaml', '/', 'glob');

        return $routes->build();
    }
}
