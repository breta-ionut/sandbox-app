<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Component\Asset\Context\ContextInterface;
use Symfony\Component\Asset\Context\RequestStackContext;
use Symfony\Component\HttpFoundation\RequestStack;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(RequestStackContext::class)
        ->args([service(RequestStack::class)]);

    $services->alias(ContextInterface::class, RequestStackContext::class);
};
