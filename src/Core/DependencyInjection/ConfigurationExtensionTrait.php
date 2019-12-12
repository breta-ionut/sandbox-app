<?php

namespace App\Core\DependencyInjection;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\LogicException;

/**
 * An implementation of the ConfigurationExtensionInterface for Core container extensions.
 */
trait ConfigurationExtensionTrait
{
    /**
     * @param array            $config
     * @param ContainerBuilder $container
     *
     * @return ConfigurationInterface|null
     *
     * @throws LogicException
     */
    public function getConfiguration(array $config, ContainerBuilder $container)
    {
        $class = str_replace('Extension', 'Configuration', get_class($this));
        $reflection = $container->getReflectionClass($class);

        if (null === $reflection) {
            throw new LogicException(sprintf('No extension configuration class "%s" defined.', $class));
        }

        if (!$reflection->implementsInterface(ConfigurationInterface::class)) {
            throw new LogicException(sprintf(
                'The extension configuration class "%s" must implement "%s".',
                $class,
                ConfigurationInterface::class
            ));
        }

        if (!$reflection->isInstantiable()
            || (($constructor = $reflection->getConstructor()) && $constructor->getNumberOfRequiredParameters())
        ) {
            throw new LogicException(sprintf(
                'The extension configuration class "%s" is not instantiable or its constructor requires arguments.',
                $class
            ));
        }

        return $reflection->newInstance();
    }
}
