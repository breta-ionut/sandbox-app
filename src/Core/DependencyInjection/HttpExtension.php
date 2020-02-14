<?php

namespace App\Core\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\SessionHandlerFactory;
use Symfony\Component\HttpFoundation\Session\Storage\MockFileSessionStorage;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use Symfony\Component\HttpFoundation\Session\Storage\SessionStorageInterface;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

class HttpExtension extends ConfigurableExtension
{
    use ConfigurationExtensionTrait;

    /**
     * {@inheritDoc}
     */
    protected function loadInternal(array $mergedConfig, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('http.yaml');

        $this->configureSession($container, $mergedConfig['session']);

        $container->registerForAutoconfiguration(ArgumentValueResolverInterface::class)
            ->addTag('controller.argument_value_resolver');
    }

    /**
     * @param ContainerBuilder $container
     * @param array            $config
     *
     * @return Reference
     */
    private function createSessionStorageHandler(ContainerBuilder $container, array $config): Reference
    {
        if (isset($config['id'])) {
            return new Reference($config['id']);
        }

        $handler = (new Definition(\SessionHandlerInterface::class, [$config['url']]))
            ->setFactory([SessionHandlerFactory::class, 'createHandler']);

        $id = \SessionHandlerInterface::class;
        $container->setDefinition($id, $handler);

        return new Reference($id);
    }

    /**
     * @param ContainerBuilder $container
     * @param array            $config
     */
    private function configureSession(ContainerBuilder $container, array $config): void
    {
        // Determine and configure the session storage.
        if ($config['test']) {
            $storageId = MockFileSessionStorage::class;
        } else {
            $storageId = NativeSessionStorage::class;

            $storageHandler = $this->createSessionStorageHandler($container, $config['handler']);
            unset($config['test'], $config['handler']);

            $container->getDefinition($storageId)
                ->setArgument('$options', $config)
                ->setArgument('$handler', $storageHandler);
        }

        $container->setAlias(SessionStorageInterface::class, $storageId);
    }
}
