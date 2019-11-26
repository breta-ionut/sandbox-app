<?php

namespace App\Core\DependencyInjection;

use Symfony\Component\Asset\Context\ContextInterface;
use Symfony\Component\Asset\PackageInterface;
use Symfony\Component\Asset\PathPackage;
use Symfony\Component\Asset\UrlPackage;
use Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy;
use Symfony\Component\Asset\VersionStrategy\JsonManifestVersionStrategy;
use Symfony\Component\Asset\VersionStrategy\StaticVersionStrategy;
use Symfony\Component\Asset\VersionStrategy\VersionStrategyInterface;
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

        // Build and register the version strategy definition.
        $versionStrategy = $this->createVersionStrategy($mergedConfig);

        $container->setDefinition($versionStrategy->getClass(), $versionStrategy);
        $container->setAlias(VersionStrategyInterface::class, $versionStrategy->getClass());

        // Build and register the application assets package definition.
        $package = $this->createPackage($mergedConfig);

        $container->setDefinition($package->getClass(), $package);
        $container->setAlias(PackageInterface::class, $package->getClass());
    }

    /**
     * @param array $config
     *
     * @return Definition
     */
    private function createVersionStrategy(array $config): Definition
    {
        switch (true) {
            case isset($config['version']):
                return new Definition(StaticVersionStrategy::class, [$config['version'], $config['version_format']]);

            case isset($config['json_manifest_path']):
                return new Definition(JsonManifestVersionStrategy::class, [$config['json_manifest_path']]);

            default:
                return new Definition(EmptyVersionStrategy::class);
        }
    }

    /**
     * @param array $config
     *
     * @return Definition
     */
    private function createPackage(array $config): Definition
    {
        $versionStrategy = new Reference(VersionStrategyInterface::class);
        $context = new Reference(ContextInterface::class);

        if ($config['base_urls']) {
            return new Definition(UrlPackage::class, [$config['base_urls'], $versionStrategy, $context]);
        }

        return new Definition(PathPackage::class, [$config['base_path'], $versionStrategy, $context]);
    }
}
