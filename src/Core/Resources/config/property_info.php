<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\Extractor\SerializerExtractor;
use Symfony\Component\PropertyInfo\PropertyAccessExtractorInterface;
use Symfony\Component\PropertyInfo\PropertyDescriptionExtractorInterface;
use Symfony\Component\PropertyInfo\PropertyInfoCacheExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractorInterface;
use Symfony\Component\PropertyInfo\PropertyInitializableExtractorInterface;
use Symfony\Component\PropertyInfo\PropertyListExtractorInterface;
use Symfony\Component\PropertyInfo\PropertyTypeExtractorInterface;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactoryInterface;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    // Extractors.
    $services->set(PhpDocExtractor::class)
        ->tag('property_info.type_extractor', ['priority' => -1000])
        ->tag('property_info.description_extractor', ['priority' => -1000]);

    $services->set(ReflectionExtractor::class)
        ->tag('property_info.list_extractor', ['priority' => -1002])
        ->tag('property_info.type_extractor', ['priority' => -1002])
        ->tag('property_info.access_extractor', ['priority' => -1000])
        ->tag('property_info.initializable_extractor', ['priority' => -1000]);

    $services->set(SerializerExtractor::class)
        ->args([service(ClassMetadataFactoryInterface::class)])
        ->tag('property_info.list_extractor', ['priority' => -1000]);
    // End of - Extractors.

    $services->set(PropertyInfoExtractor::class)
        ->args([
            tagged_iterator('property_info.list_extractor'),
            tagged_iterator('property_info.type_extractor'),
            tagged_iterator('property_info.description_extractor'),
            tagged_iterator('property_info.access_extractor'),
            tagged_iterator('property_info.initializable_extractor'),
        ]);

    $services->alias(PropertyTypeExtractorInterface::class, PropertyInfoExtractor::class);
    $services->alias(PropertyDescriptionExtractorInterface::class, PropertyInfoExtractor::class);
    $services->alias(PropertyAccessExtractorInterface::class, PropertyInfoExtractor::class);
    $services->alias(PropertyListExtractorInterface::class, PropertyInfoExtractor::class);
    $services->alias(PropertyInfoExtractorInterface::class, PropertyInfoExtractor::class);
    $services->alias(PropertyInitializableExtractorInterface::class, PropertyInfoExtractor::class);

    $services->set(PropertyInfoCacheExtractor::class)
        ->decorate(PropertyInfoExtractor::class)
        ->args([service(\sprintf('%s.inner', PropertyInfoCacheExtractor::class)), service('cache.property_info')]);
};
