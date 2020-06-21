<?php

declare(strict_types=1);

namespace App\Core;

use App\Core\Command\Cache\CachePoolPruneCommand;
use App\Core\DependencyInjection\AssetsExtension;
use App\Core\DependencyInjection\CacheExtension;
use App\Core\DependencyInjection\Compiler\RegisterDoctrineListenersAndSubscribersPass;
use App\Core\DependencyInjection\Compiler\ServiceEntityRepositoriesPass;
use App\Core\DependencyInjection\ConsoleExtension;
use App\Core\DependencyInjection\CoreExtension;
use App\Core\DependencyInjection\DoctrineExtension;
use App\Core\DependencyInjection\DoctrineMigrationsExtension;
use App\Core\DependencyInjection\HttpExtension;
use App\Core\DependencyInjection\PropertyAccessExtension;
use App\Core\DependencyInjection\PropertyInfoExtension;
use App\Core\DependencyInjection\RoutingExtension;
use App\Core\DependencyInjection\SecurityExtension;
use App\Core\DependencyInjection\SerializerExtension;
use App\Core\DependencyInjection\TemplatingExtension;
use App\Core\DependencyInjection\ValidatorExtension;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Cache\DependencyInjection\CachePoolClearerPass;
use Symfony\Component\Cache\DependencyInjection\CachePoolPass;
use Symfony\Component\Cache\DependencyInjection\CachePoolPrunerPass;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Console\CommandLoader\CommandLoaderInterface;
use Symfony\Component\Console\DependencyInjection\AddConsoleCommandPass;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\EventDispatcher\DependencyInjection\RegisterListenersPass;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver\ServiceValueResolver;
use Symfony\Component\HttpKernel\DependencyInjection\ControllerArgumentValueResolverPass;
use Symfony\Component\HttpKernel\DependencyInjection\RegisterControllerArgumentLocatorsPass;
use Symfony\Component\HttpKernel\DependencyInjection\ResettableServicePass;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\DependencyInjection\RoutingResolverPass;
use Symfony\Component\Serializer\DependencyInjection\SerializerPass;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\ContainerConstraintValidatorFactory;
use Symfony\Component\Validator\DependencyInjection\AddConstraintValidatorsPass;
use Symfony\Component\Validator\DependencyInjection\AddValidatorInitializersPass;
use Symfony\Component\Validator\ValidatorBuilder;

abstract class Kernel extends BaseKernel
{
    /**
     * {@inheritDoc}
     */
    public function registerBundles()
    {
        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $configDir = $this->getConfigDir();

        $loader->load($configDir.'/{packages}/*.yaml', 'glob');
        $loader->load($configDir.'/{packages}/'.$this->environment.'/**/*.yaml', 'glob');
    }

    /**
     * {@inheritDoc}
     */
    protected function getHttpKernel()
    {
        return $this->container->get(HttpKernelInterface::class);
    }

    /**
     * {@inheritDoc}
     */
    protected function build(ContainerBuilder $container)
    {
        // Register the core extensions.
        foreach ($this->getExtensions() as $extension) {
            $container->registerExtension($extension);
        }

        // Register the core compiler passes.
        foreach ($this->getCompilerPasses() as $compilerPassDefinition) {
            if ($compilerPassDefinition instanceof CompilerPassInterface) {
                $compilerPass = $compilerPassDefinition;
                $type = PassConfig::TYPE_BEFORE_OPTIMIZATION;
                $priority = 0;
            } else {
                $compilerPass = $compilerPassDefinition[0];
                $type = $compilerPassDefinition[1] ?? PassConfig::TYPE_BEFORE_OPTIMIZATION;
                $priority = $compilerPassDefinition[2] ?? 0;
            }

            $container->addCompilerPass($compilerPass, $type, $priority);
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function getKernelParameters()
    {
        return \array_merge(parent::getKernelParameters(), ['kernel.config_dir' => $this->getConfigDir()]);
    }

    /**
     * Returns the path to the directory where the app configuration is kept.
     *
     * @return string
     */
    private function getConfigDir(): string
    {
        return $this->getProjectDir().'/config';
    }

    /**
     * Returns the core container extensions.
     *
     * @return ExtensionInterface[]
     */
    private function getExtensions(): array
    {
        return [
            new CoreExtension(),
            new HttpExtension(),
            new ConsoleExtension(),
            new RoutingExtension(),
            new DoctrineExtension(),
            new DoctrineMigrationsExtension(),
            new SecurityExtension(),
            new CacheExtension(),
            new PropertyInfoExtension(),
            new PropertyAccessExtension(),
            new SerializerExtension(),
            new ValidatorExtension(),
            new TemplatingExtension(),
            new AssetsExtension(),
        ];
    }

    /**
     * Returns the core compiler passes. For each pass the type and priority can be also specified.
     *
     * @return array
     */
    private function getCompilerPasses(): array
    {
        return [
            new ResettableServicePass(),
            new RegisterListenersPass(EventDispatcherInterface::class),
            new ControllerArgumentValueResolverPass(ArgumentResolver::class),
            new RegisterControllerArgumentLocatorsPass(ServiceValueResolver::class),
            new AddConsoleCommandPass(CommandLoaderInterface::class),
            new RoutingResolverPass(),
            new ServiceEntityRepositoriesPass(),
            new RegisterDoctrineListenersAndSubscribersPass(),
            new CachePoolPass(
                'cache.pool',
                'kernel.reset',
                'cache.clearer.global',
                'cache.pool.clearer',
                'cache.clearer.system'
            ),
            [new CachePoolClearerPass(), PassConfig::TYPE_AFTER_REMOVING],
            [new CachePoolPrunerPass(CachePoolPruneCommand::class), PassConfig::TYPE_AFTER_REMOVING],
            new SerializerPass(Serializer::class),
            new AddConstraintValidatorsPass(ContainerConstraintValidatorFactory::class),
            new AddValidatorInitializersPass(ValidatorBuilder::class),
        ];
    }
}
