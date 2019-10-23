<?php

namespace App\Core\DependencyInjection;

use App\Core\DependencyInjection\Compiler\RegisterDoctrineListenersAndSubscribersPass;
use App\Core\DependencyInjection\Compiler\ServiceEntityRepositoriesPass;
use App\Core\Doctrine\EntityManagerFactory;
use App\Core\Doctrine\ServiceEntityRepository;
use Doctrine\Common\EventSubscriber;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
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

        // Determine and inject the connection parameters.
        $connectionParams = [
            'driver' => $mergedConfig['driver'],
            'url' => $mergedConfig['url'],
        ];
        foreach (['server_version', 'charset', 'default_table_options'] as $configKey) {
            if (isset($mergedConfig[$configKey])) {
                $connectionParams[$container::camelize($configKey)] = $mergedConfig[$configKey];
            }
        }

        $container->getDefinition(EntityManagerFactory::class)->setArgument('$connectionParams', $connectionParams);

        $container->registerForAutoconfiguration(ServiceEntityRepository::class)
            ->addTag(ServiceEntityRepositoriesPass::SERVICE_ENTITY_REPOSITORY_TAG);
        $container->registerForAutoconfiguration(EventSubscriber::class)
            ->addTag(RegisterDoctrineListenersAndSubscribersPass::SUBSCRIBER_TAG);
    }
}
