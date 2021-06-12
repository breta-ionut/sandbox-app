<?php

declare(strict_types=1);

namespace App\Core\Routing;

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Symfony\Component\Routing\Loader\PhpFileLoader;
use Symfony\Component\Routing\RouteCollection;

class Loader
{
    private PhpFileLoader $phpFileLoader;
    private string $configDir;
    private string $environment;

    public function __construct(PhpFileLoader $phpFileLoader, string $configDir, string $environment)
    {
        $this->phpFileLoader = $phpFileLoader;
        $this->configDir = $configDir;
        $this->environment = $environment;
    }

    public function load(): RouteCollection
    {
        $routes = new RouteCollection();
        $file = (new \ReflectionObject($this))->getFileName();
        $configurator = new RoutingConfigurator($routes, $this->phpFileLoader, $file, $file);

        $configurator->import($this->configDir.'/{routes}/*.yaml', 'glob');
        $configurator->import($this->configDir.'/{routes}/'.$this->environment.'/**/*.yaml', 'glob');

        return $routes;
    }
}
