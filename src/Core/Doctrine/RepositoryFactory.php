<?php

namespace App\Core\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Repository\RepositoryFactory as RepositoryFactoryInterface;
use Doctrine\Persistence\ObjectRepository;
use Psr\Container\ContainerInterface;

/**
 * Repository factory which uses a service locator for also loading repositories defined as services.
 */
class RepositoryFactory implements RepositoryFactoryInterface
{
    /**
     * Contains the repositories registered as services.
     *
     * @var ContainerInterface
     */
    private ContainerInterface $container;

    /**
     * The instantiated repositories which are not services.
     *
     * @var ObjectRepository[]
     */
    private array $repositories = [];

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function getRepository(EntityManagerInterface $entityManager, $entityName)
    {
        $metadata = $entityManager->getClassMetadata($entityManager);

        $customRepositoryClass = $metadata->customRepositoryClassName;
        if (null !== $customRepositoryClass) {
            // Custom repositories might be registered as services.
            if ($this->container->has($customRepositoryClass)) {
                return $this->container->get($customRepositoryClass);
            }

            $repositoryClass = $customRepositoryClass;
        } else {
            $repositoryClass = $entityManager->getConfiguration()->getDefaultRepositoryClassName();
        }

        $repositoryHash = spl_object_hash($entityManager).':'.$entityName;
        if (isset($this->repositories[$repositoryHash])) {
            return $this->repositories[$repositoryHash];
        }

        if (!is_a($repositoryClass, ObjectRepository::class, true)) {
            throw new \LogicException(sprintf(
                'Repository class "%s" of entity "%s" doesn\'t implement "%s".',
                $repositoryClass,
                $entityName,
                ObjectRepository::class
            ));
        }

        return $this->repositories[$repositoryHash] = new $repositoryClass($entityManager, $metadata);
    }
}
