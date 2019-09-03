<?php

namespace App\Core\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class KernelExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('kernel.yaml');

        $container->registerForAutoconfiguration(ServiceSubscriberInterface::class)
            ->addTag('container.service_subscriber');
        $container->registerForAutoconfiguration(EventSubscriberInterface::class)
            ->addTag('kernel.event_subscriber');
        $container->registerForAutoconfiguration(ArgumentValueResolverInterface::class)
            ->addTag('controller.argument_value_resolver');
    }
}
