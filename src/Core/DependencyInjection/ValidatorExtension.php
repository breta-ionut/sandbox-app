<?php

declare(strict_types=1);

namespace App\Core\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;
use Symfony\Component\Validator\Constraints\EmailValidator;
use Symfony\Component\Validator\ValidatorBuilder;

class ValidatorExtension extends ConfigurableExtension
{
    use ConfigurationExtensionTrait;

    /**
     * {@inheritDoc}
     */
    protected function loadInternal(array $mergedConfig, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('validator.yaml');

        $container->getDefinition(ValidatorBuilder::class)
            ->addMethodCall('addYamlMapping', [$this->getMappingFiles($container, $mergedConfig['mapping_dir'])]);

        $container->getDefinition(EmailValidator::class)
            ->setArgument('$defaultMode', $mergedConfig['email_validation_mode']);
    }

    /**
     * @param ContainerBuilder $container
     * @param string           $mappingDir
     *
     * @return string[]
     */
    private function getMappingFiles(ContainerBuilder $container, string $mappingDir): array
    {
        if (!$container->fileExists($mappingDir, '/^$/')) {
            return [];
        }

        $mappingFiles = Finder::create()
            ->files()
            ->in($mappingDir)
            ->name('/\.yaml$/')
            ->sortByName();

        return \array_map(
            fn(\SplFileInfo $mappingFile): string => $mappingFile->getRealPath(),
            \iterator_to_array($mappingFiles)
        );
    }
}
