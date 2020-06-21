<?php

declare(strict_types=1);

namespace App\Core\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;
use Symfony\Component\Validator\Constraints\EmailValidator;
use Symfony\Component\Validator\ConstraintValidatorInterface;
use Symfony\Component\Validator\ObjectInitializerInterface;
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

        $validatorBuilder = $container->getDefinition(ValidatorBuilder::class);

        $validatorBuilder->addMethodCall(
            'addYamlMapping',
            [$this->getMappingFiles($container, $mergedConfig['mapping_dir'])]
        );
        if (!$container->getParameter('kernel.debug')) {
            $validatorBuilder->addMethodCall('setMappingCache', [new Reference('validator.mapping.cache')]);
        }

        $container->getDefinition(EmailValidator::class)
            ->setArgument('$defaultMode', $mergedConfig['email_validation_mode']);

        $container->registerForAutoconfiguration(ConstraintValidatorInterface::class)
            ->addTag('validator.constraint_validator');
        $container->registerForAutoconfiguration(ObjectInitializerInterface::class)->addTag('validator.initializer');
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
