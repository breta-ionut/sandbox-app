<?php

declare(strict_types=1);

namespace App\Core\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;
use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\Serializer\Encoder\EncoderInterface;
use Symfony\Component\Serializer\Mapping\Factory\CacheClassMetadataFactory;
use Symfony\Component\Serializer\NameConverter\MetadataAwareNameConverter;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class SerializerExtension extends ConfigurableExtension
{
    use ConfigurationExtensionTrait;

    /**
     * {@inheritDoc}
     */
    protected function loadInternal(array $mergedConfig, ContainerBuilder $container): void
    {
        $loader = new PhpFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('serializer.php');

        if (isset($mergedConfig['name_converter'])) {
            $container->getDefinition(MetadataAwareNameConverter::class)
                ->setArgument('$fallbackNameConverter', new Reference($mergedConfig['name_converter']));
        }

        $this->configureObjectNormalizer($container->getDefinition(ObjectNormalizer::class), $mergedConfig);

        if ($container->getParameter('kernel.debug')) {
            $container->removeDefinition(CacheClassMetadataFactory::class);
        }

        $container->registerForAutoconfiguration(NormalizerInterface::class)->addTag('serializer.normalizer');
        $container->registerForAutoconfiguration(DenormalizerInterface::class)->addTag('serializer.normalizer');
        $container->registerForAutoconfiguration(EncoderInterface::class)->addTag('serializer.encoder');
        $container->registerForAutoconfiguration(DecoderInterface::class)->addTag('serializer.encoder');
    }

    private function configureObjectNormalizer(Definition $objectNormalizer, array $config): void
    {
        $defaultContext = [];
        foreach (['circular_reference_handler', 'max_depth_handler'] as $handlerKey) {
            if (isset($config[$handlerKey])) {
                $defaultContext[$handlerKey] = new Reference($config[$handlerKey]);
            }
        }

        $objectNormalizer->setArgument('$defaultContext', $defaultContext);
    }
}
