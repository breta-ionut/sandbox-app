<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\PhpArrayAdapter;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\PropertyInfo\PropertyInfoExtractorInterface;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\Serializer\Encoder\EncoderInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\YamlEncoder;
use Symfony\Component\Serializer\Mapping\ClassDiscriminatorFromClassMetadata;
use Symfony\Component\Serializer\Mapping\ClassDiscriminatorResolverInterface;
use Symfony\Component\Serializer\Mapping\Factory\CacheClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactoryInterface;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\NameConverter\AdvancedNameConverterInterface;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\NameConverter\MetadataAwareNameConverter;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\ConstraintViolationListNormalizer;
use Symfony\Component\Serializer\Normalizer\CustomNormalizer;
use Symfony\Component\Serializer\Normalizer\DataUriNormalizer;
use Symfony\Component\Serializer\Normalizer\DateIntervalNormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeZoneNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\JsonSerializableNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\ProblemNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

return static function (ContainerConfigurator $container): void {
    $container->parameters()
        ->set('serializer.mapping.cache.file', param('kernel.cache_dir').'/serialization.php');

    $services = $container->services();

    // Mapping.
    $services->set(AnnotationLoader::class);

    $services->set(ClassMetadataFactory::class)
        ->args([service(AnnotationLoader::class)]);

    $services->alias(ClassMetadataFactoryInterface::class, ClassMetadataFactory::class);

    $services->set('serializer.mapping.cache', CacheItemPoolInterface::class)
        ->factory([PhpArrayAdapter::class, 'create'])
        ->args([param('serializer.mapping.cache.file'), service('cache.serializer')]);

    $services->set(CacheClassMetadataFactory::class)
        ->decorate(ClassMetadataFactory::class)
        ->args([service(\sprintf('%s.inner', CacheClassMetadataFactory::class)), service('serializer.mapping.cache')]);

    $services->set(ClassDiscriminatorFromClassMetadata::class)
        ->args([service(ClassMetadataFactoryInterface::class)]);

    $services->alias(ClassDiscriminatorResolverInterface::class, ClassDiscriminatorFromClassMetadata::class);
    // End of - Mapping.

    $services->set(CamelCaseToSnakeCaseNameConverter::class);

    $services->set(MetadataAwareNameConverter::class)
        ->args([service(ClassMetadataFactoryInterface::class)]);

    $services->alias(NameConverterInterface::class, MetadataAwareNameConverter::class);
    $services->alias(AdvancedNameConverterInterface::class, MetadataAwareNameConverter::class);

    // Normalizers.
    $services->set(ArrayDenormalizer::class)
        ->tag('serializer.normalizer', ['priority' => -900]);

    $services->set(ConstraintViolationListNormalizer::class)
        ->args([[], service(NameConverterInterface::class)])
        ->tag('serializer.normalizer', ['priority' => -900]);

    $services->set(CustomNormalizer::class)
        ->tag('serializer.normalizer', ['priority' => -900]);

    $services->set(DataUriNormalizer::class)
        ->tag('serializer.normalizer', ['priority' => -900]);

    $services->set(DateIntervalNormalizer::class)
        ->tag('serializer.normalizer', ['priority' => -900]);

    $services->set(DateTimeNormalizer::class)
        ->tag('serializer.normalizer', ['priority' => -900]);

    $services->set(DateTimeZoneNormalizer::class)
        ->tag('serializer.normalizer', ['priority' => -900]);

    $services->set(JsonSerializableNormalizer::class)
        ->tag('serializer.normalizer', ['priority' => -900]);

    $services->set(ObjectNormalizer::class)
        ->args([
            service(ClassMetadataFactoryInterface::class),
            service(NameConverterInterface::class),
            service(PropertyAccessorInterface::class),
            service(PropertyInfoExtractorInterface::class),
            service(ClassDiscriminatorResolverInterface::class),
            null,
            abstract_arg('default context'),
        ])
        ->tag('serializer.normalizer', ['priority' => -1000]);

    $services->set(ProblemNormalizer::class)
        ->args([param('kernel.debug')])
        ->tag('serializer.normalizer', ['priority' => -900]);
    // End of - Normalizers.

    // Encoders.
    $services->set(CsvEncoder::class)
        ->tag('serializer.encoder');

    $services->set(JsonEncoder::class)
        ->tag('serializer.encoder');

    $services->set(XmlEncoder::class)
        ->tag('serializer.encoder');

    $services->set(YamlEncoder::class)
        ->tag('serializer.encoder');
    // End of - Encoders.

    $services->set(Serializer::class)
        ->args([abstract_arg('normalizers'), abstract_arg('encoders')]);

    $services->alias(SerializerInterface::class, Serializer::class);
    $services->alias(NormalizerInterface::class, Serializer::class);
    $services->alias(DenormalizerInterface::class, Serializer::class);
    $services->alias(EncoderInterface::class, Serializer::class);
    $services->alias(DecoderInterface::class, Serializer::class);
};
