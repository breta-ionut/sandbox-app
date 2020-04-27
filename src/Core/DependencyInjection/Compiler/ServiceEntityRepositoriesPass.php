<?php

declare(strict_types=1);

namespace App\Core\DependencyInjection\Compiler;

use App\Core\Doctrine\RepositoryFactory;
use Doctrine\Persistence\ObjectRepository;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Reference;

class ServiceEntityRepositoriesPass implements CompilerPassInterface
{
    public const SERVICE_ENTITY_REPOSITORY_TAG = 'doctrine.service_entity_repository';

    private const REPOSITORY_FACTORY_ID = RepositoryFactory::class;

    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        $parameterBag = $container->getParameterBag();
        $repositories = [];

        foreach (\array_keys($container->findTaggedServiceIds(self::SERVICE_ENTITY_REPOSITORY_TAG)) as $id) {
            $class = $parameterBag->resolveValue($container->getDefinition($id)->getClass());
            if (!\is_a($class, ObjectRepository::class, true)) {
                throw new LogicException(\sprintf(
                    '"%s" was registered as a Doctrine service entity repository but doesn\'t implement "%s".',
                    $id,
                    ObjectRepository::class
                ));
            }

            $repositories[$id] = new Reference($id);
        }

        $container->getDefinition(self::REPOSITORY_FACTORY_ID)
            ->setArgument('$container', ServiceLocatorTagPass::register($container, $repositories));
    }
}
