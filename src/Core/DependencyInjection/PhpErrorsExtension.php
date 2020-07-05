<?php

declare(strict_types=1);

namespace App\Core\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;
use Symfony\Component\HttpKernel\EventListener\DebugHandlersListener;

class PhpErrorsExtension extends ConfigurableExtension
{
    /**
     * {@inheritDoc}
     */
    public function getConfiguration(array $config, ContainerBuilder $container)
    {
        return new PhpErrorsConfiguration($container->getParameter('kernel.debug'));
    }

    /**
     * {@inheritDoc}
     */
    protected function loadInternal(array $mergedConfig, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('php_errors.yaml');

        $debugHandlersListener = $container->getDefinition(DebugHandlersListener::class);

        if (false === $mergedConfig['log']) {
            $debugHandlersListener->setArgument('$logger', null)
                ->setArgument('$deprecationLogger', null);
        } elseif (\is_int($mergedConfig['log'])) {
            $debugHandlersListener->setArgument('$levels', $mergedConfig['log']);
        }

        if (!$mergedConfig['throw']) {
            $container->setParameter('php_errors.throw_at', 0);
        }
    }
}
