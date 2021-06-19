<?php

declare(strict_types=1);

namespace App\Core\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\PropertyInfo\PropertyAccessExtractorInterface;
use Symfony\Component\PropertyInfo\PropertyDescriptionExtractorInterface;
use Symfony\Component\PropertyInfo\PropertyInfoCacheExtractor;
use Symfony\Component\PropertyInfo\PropertyInitializableExtractorInterface;
use Symfony\Component\PropertyInfo\PropertyListExtractorInterface;
use Symfony\Component\PropertyInfo\PropertyTypeExtractorInterface;

class PropertyInfoExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new PhpFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('property_info.php');

        if ($container->getParameter('kernel.debug')) {
            $container->removeDefinition(PropertyInfoCacheExtractor::class);
        }

        $container->registerForAutoconfiguration(PropertyListExtractorInterface::class)
            ->addTag('property_info.list_extractor');
        $container->registerForAutoconfiguration(PropertyTypeExtractorInterface::class)
            ->addTag('property_info.type_extractor');
        $container->registerForAutoconfiguration(PropertyDescriptionExtractorInterface::class)
            ->addTag('property_info.description_extractor');
        $container->registerForAutoconfiguration(PropertyAccessExtractorInterface::class)
            ->addTag('property_info.access_extractor');
        $container->registerForAutoconfiguration(PropertyInitializableExtractorInterface::class)
            ->addTag('property_info.initializable_extractor');
    }
}
