<?php

declare(strict_types=1);

namespace App\Core\DependencyInjection;

use App\Core\DependencyInjection\Compiler\RegisterDoctrineListenersAndSubscribersPass;
use App\Core\DependencyInjection\Compiler\ServiceEntityRepositoriesPass;
use App\Core\Doctrine\RepositoryFactory;
use App\Core\Doctrine\ServiceEntityRepository;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\XmlDriver;
use Doctrine\ORM\Mapping\UnderscoreNamingStrategy;
use Doctrine\ORM\Tools\Setup;
use Doctrine\Persistence\Mapping\Driver\SymfonyFileLocator;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Parameter;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

class DoctrineExtension extends ConfigurableExtension
{
    use ConfigurationExtensionTrait;

    /**
     * {@inheritDoc}
     */
    protected function loadInternal(array $mergedConfig, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('doctrine.yaml');

        $this->configureEntityManager($container, $mergedConfig);

        $container->registerForAutoconfiguration(ServiceEntityRepository::class)
            ->addTag(ServiceEntityRepositoriesPass::SERVICE_ENTITY_REPOSITORY_TAG);
        $container->registerForAutoconfiguration(EventSubscriber::class)
            ->addTag(RegisterDoctrineListenersAndSubscribersPass::SUBSCRIBER_TAG);
    }

    /**
     * @param ContainerBuilder $container
     * @param array            $config
     */
    private function configureEntityManager(ContainerBuilder $container, array $config): void
    {
        $fileLocator = new Definition(SymfonyFileLocator::class, [
            $this->getMappingPrefixes(
                $container,
                $config['orm']['mapping_dir'],
                $config['orm']['namespace_prefix_pattern']
            ),
            '.xml',
        ]);
        $metadataDriver = new Definition(XmlDriver::class, [$fileLocator]);

        $entityManagerConfig =
            (new Definition(
                Configuration::class,
                [new Parameter('kernel.debug'), new Parameter('doctrine.orm.proxy_dir')]
            ))
            ->setFactory([Setup::class, 'createConfiguration'])
            ->addMethodCall('setMetadataDriverImpl', [$metadataDriver])
            ->addMethodCall('setNamingStrategy', [new Definition(UnderscoreNamingStrategy::class, [\CASE_LOWER, true])])
            ->addMethodCall('setRepositoryFactory', [new Reference(RepositoryFactory::class)]);

        $cacheConfigurators = [
            'metadata' => 'setMetadataCacheImpl',
            'query' => 'setQueryCacheImpl',
            'result' => 'setHydrationCacheImpl',
        ];
        foreach ($cacheConfigurators as $cacheType => $method) {
            $configKey = \sprintf('%s_cache_driver', $cacheType);

            if (isset($config['orm'][$configKey])) {
                $entityManagerConfig->addMethodCall($method, [new Reference($config['orm'][$configKey])]);
            }
        }

        $container->getDefinition(EntityManager::class)
            ->setArgument('$connection', $this->getConnectionParams($config['database']))
            ->setArgument('$config', $entityManagerConfig);
    }

    /**
     * @param ContainerBuilder $container
     * @param string           $mappingDir
     * @param string           $namespacePrefixPattern
     *
     * @return string[]
     */
    private function getMappingPrefixes(
        ContainerBuilder $container,
        string $mappingDir,
        string $namespacePrefixPattern
    ): array {
        $mappingDir = $container->getParameterBag()->resolveString($mappingDir);

        if (!$container->fileExists($mappingDir, '/^$/')) {
            return [];
        }

        $prefixes = [];
        $directories = Finder::create()
            ->directories()
            ->in($mappingDir)
            ->depth(0)
            ->sortByName();

        foreach ($directories as $directory) {
            $prefixes[$directory->getRealPath()] = \sprintf($namespacePrefixPattern, $directory->getFilename());
        }

        return $prefixes;
    }

    /**
     * @param array $databaseConfig
     *
     * @return array
     */
    private function getConnectionParams(array $databaseConfig): array
    {
        $connectionParams = [
            'driver' => $databaseConfig['driver'],
            'url' => $databaseConfig['url'],
        ];

        foreach (['server_version', 'charset', 'default_table_options'] as $configKey) {
            if (isset($databaseConfig[$configKey])) {
                $connectionParams[ContainerBuilder::camelize($configKey)] = $databaseConfig[$configKey];
            }
        }

        return $connectionParams;
    }
}
