<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

return static function (ContainerConfigurator $container): void {
    $container->parameters()
        ->set('property_access.magic_call', false)
        ->set('property_access.throw_exception_on_invalid_index', false)
        ->set('property_access.throw_exception_on_invalid_property_path', true);

    $services = $container->services();

    $services->set(PropertyAccessor::class)
        ->args([
            param('property_access.magic_call'),
            param('property_access.throw_exception_on_invalid_index'),
            service('cache.property_access'),
            param('property_access.throw_exception_on_invalid_property_path'),
        ]);

    $services->alias(PropertyAccessorInterface::class, PropertyAccessor::class);
};
