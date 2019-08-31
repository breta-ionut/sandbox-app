<?php

namespace App\Core;

use App\Core\DependencyInjection\KernelExtension;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

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
        ];
    }

    /**
     * Returns the application's core compiler passes.
     *
     * @return CompilerPassInterface[]
     */
    private function getCompilerPasses(): array
    {
        return [];
    }
}
