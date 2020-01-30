<?php

namespace App\Core\DependencyInjection;

use Symfony\Component\Asset\Context\ContextInterface;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Asset\PathPackage;
use Symfony\Component\Asset\UrlPackage;
use Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy;
use Symfony\Component\Asset\VersionStrategy\JsonManifestVersionStrategy;
use Symfony\Component\Asset\VersionStrategy\StaticVersionStrategy;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

class AssetsExtension extends ConfigurableExtension
{
    use ConfigurationExtensionTrait;

    /**
     * {@inheritDoc}
     */
    protected function loadInternal(array $mergedConfig, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('assets.yaml');

        $defaultPackage = $this->createPackage($container, '_default', $mergedConfig);

        $packages = [];
        foreach ($mergedConfig['packages'] as $packageName => $packageConfig) {
            $packages[] = $this->createPackage($container, $packageName, $packageConfig);
        }

        $container->setDefinition(Packages::class, new Definition(null, [$defaultPackage, $packages]));
    }

    /**
     * @param ContainerBuilder $container
     * @param string           $packageName
     * @param array            $config
     *
     * @return Reference
     */
    private function createVersionStrategy(ContainerBuilder $container, string $packageName, array $config): Reference
    {
        if (isset($config['version'])) {
            $definition = new Definition(StaticVersionStrategy::class, [$config['version'], $config['version_format']]);
        } elseif (isset($config['json_manifest_path'])) {
            $definition = new Definition(JsonManifestVersionStrategy::class, [$config['json_manifest_path']]);
        } else {
            $definition = new Definition(EmptyVersionStrategy::class);
        }

        $id = 'assets.version.'.$packageName;
        $container->setDefinition($id, $definition);

        return new Reference($id);
    }

    /**
     * @param ContainerBuilder $container
     * @param string           $name
     * @param array            $config
     *
     * @return Reference
     */
    private function createPackage(ContainerBuilder $container, string $name, array $config): Reference
    {
        $versionStrategy = $this->createVersionStrategy($container, $name, $config);
        $context = new Reference(ContextInterface::class);

        $definition = $config['base_urls']
            ? new Definition(UrlPackage::class, [$config['base_urls'], $versionStrategy, $context])
            : new Definition(PathPackage::class, [$config['base_path'], $versionStrategy, $context]);

        $id = 'assets.package.'.$name;
        $container->setDefinition($id, $definition);

        return new Reference($id);
    }
}
