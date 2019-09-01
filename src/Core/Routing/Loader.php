<?php

namespace App\Core\Routing;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouteCollectionBuilder;

class Loader
{
    /**
     * @var string
     */
    private $configDir;

    /**
     * @var string
     */
    private $environment;

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
        $routeCollectionBuilder = new RouteCollectionBuilder($loader);

        $routeCollectionBuilder->import($this->configDir.'/{routes}/*.yaml', 'glob');
        $routeCollectionBuilder->import($this->configDir.'/{routes}/'.$this->environment.'/**/*.yaml', 'glob');

        return $routeCollectionBuilder->build();
    }
}
