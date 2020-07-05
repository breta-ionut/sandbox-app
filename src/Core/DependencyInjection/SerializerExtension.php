<?php

declare(strict_types=1);

namespace App\Core\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;
use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\Serializer\Encoder\EncoderInterface;
use Symfony\Component\Serializer\Mapping\Factory\CacheClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\LoaderChain;
use Symfony\Component\Serializer\Mapping\Loader\YamlFileLoader as SerializerYamlFileLoader;
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
    protected function loadInternal(array $mergedConfig, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('serializer.yaml');

        $this->configureMappingLoader($container, $mergedConfig['mapping_dir']);

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

    /**
     * @param ContainerBuilder $container
     * @param string           $mappingDir
     */
    private function configureMappingLoader(ContainerBuilder $container, string $mappingDir): void
    {
        $mappingDir = $container->getParameterBag()->resolveString($mappingDir);

        if (!$container->fileExists($mappingDir, '/^$/')) {
            return;
        }

        $mappingFiles = Finder::create()
            ->files()
            ->in($mappingDir)
            ->name('/\.yaml$/')
            ->sortByName();
        $loaders = [];

        foreach ($mappingFiles as $mappingFile) {
            $loaders[] = new Definition(SerializerYamlFileLoader::class, [$mappingFile->getRealPath()]);
        }

        $container->getDefinition(LoaderChain::class)->setArgument('$loaders', $loaders);
    }

    /**
     * @param Definition $objectNormalizer
     * @param array      $config
     */
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
