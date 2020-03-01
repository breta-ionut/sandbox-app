<?php

declare(strict_types=1);

namespace App\Core;

use App\Core\DependencyInjection\AssetsExtension;
use App\Core\DependencyInjection\Compiler\RegisterDoctrineListenersAndSubscribersPass;
use App\Core\DependencyInjection\Compiler\ServiceEntityRepositoriesPass;
use App\Core\DependencyInjection\ConsoleExtension;
use App\Core\DependencyInjection\CoreExtension;
use App\Core\DependencyInjection\DoctrineExtension;
use App\Core\DependencyInjection\DoctrineMigrationsExtension;
use App\Core\DependencyInjection\HttpExtension;
use App\Core\DependencyInjection\RoutingExtension;
use App\Core\DependencyInjection\TemplatingExtension;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Console\CommandLoader\CommandLoaderInterface;
use Symfony\Component\Console\DependencyInjection\AddConsoleCommandPass;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\EventDispatcher\DependencyInjection\RegisterListenersPass;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver\ServiceValueResolver;
use Symfony\Component\HttpKernel\DependencyInjection\ControllerArgumentValueResolverPass;
use Symfony\Component\HttpKernel\DependencyInjection\RegisterControllerArgumentLocatorsPass;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\DependencyInjection\RoutingResolverPass;

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
        foreach ($this->getCompilerPasses() as $compilerPass) {
            $container->addCompilerPass($compilerPass);
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function getKernelParameters()
    {
        return array_merge(parent::getKernelParameters(), ['kernel.config_dir' => $this->getProjectDir().'/config']);
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
            new TemplatingExtension(),
            new AssetsExtension(),
        ];
    }

    /**
     * Returns the core compiler passes.
     *
     * @return CompilerPassInterface[]
     */
    private function getCompilerPasses(): array
    {
        return [
            new RegisterListenersPass(EventDispatcherInterface::class),
            new ControllerArgumentValueResolverPass(ArgumentResolver::class),
            new RegisterControllerArgumentLocatorsPass(ServiceValueResolver::class),
            new AddConsoleCommandPass(CommandLoaderInterface::class),
            new RoutingResolverPass(),
            new ServiceEntityRepositoriesPass(),
            new RegisterDoctrineListenersAndSubscribersPass(),
        ];
    }
}
