<?php

namespace App\Core;

use App\Core\DependencyInjection\KernelExtension;
use App\Core\DependencyInjection\RoutingExtension;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\EventDispatcher\DependencyInjection\RegisterListenersPass;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\DependencyInjection\RoutingResolverPass;

class Kernel extends BaseKernel
{
    /**
     * {@inheritDoc}
     */
    public function registerBundles()
    {
        // This app doesn't rely on and doesn't use bundles.
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
     * Returns the application's core container extensions.
     *
     * @return ExtensionInterface[]
     */
    private function getExtensions(): array
    {
        return [
            new KernelExtension(),
            new RoutingExtension(),
        ];
    }

    /**
     * Returns the application's core compiler passes.
     *
     * @return CompilerPassInterface[]
     */
    private function getCompilerPasses(): array
    {
        return [
            new RegisterListenersPass(EventDispatcherInterface::class),
            new RoutingResolverPass(),
        ];
    }
}
