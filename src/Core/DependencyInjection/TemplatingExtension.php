<?php

declare(strict_types=1);

namespace App\Core\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;
use Symfony\Component\Templating\Helper\HelperInterface;
use Symfony\Component\Templating\Loader\FilesystemLoader;

class TemplatingExtension extends ConfigurableExtension
{
    use ConfigurationExtensionTrait;

    /**
     * {@inheritDoc}
     */
    protected function loadInternal(array $mergedConfig, ContainerBuilder $container): void
    {
        $loader = new PhpFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('templating.php');

        $container->getDefinition(FilesystemLoader::class)
            ->setArgument('$templatePathPatterns', $mergedConfig['templates_dir'].'/%%name%%');

        $container->registerForAutoconfiguration(HelperInterface::class)->addTag('templating.helper');
    }
}
