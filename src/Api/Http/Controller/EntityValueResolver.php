<?php

declare(strict_types=1);

namespace App\Api\Http\Controller;

use App\Api\Exception\ResourceNotFoundException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

/**
 * Loads entities used as controller arguments by ids extracted from request attributes.
 *
 * How are request attributes mapped to entity ids: if for example an "App\Entity\Example" entity with the "id" field as
 * id needs to be loaded, the resolver will check the "exampleId" and "id" attributes for determining the id.
 */
class EntityValueResolver implements ArgumentValueResolverInterface
{
    private EntityManagerInterface $entityManager;
    private array $ids = [];

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritDoc}
     */
    public function supports(Request $request, ArgumentMetadata $argument)
    {
        $entityClass = $argument->getType();
        $supports = \class_exists($entityClass)
            && $this->entityManager->getMetadataFactory()->isTransient($entityClass)
            && null !== ($id = $this->extractIdFromRequest($request, $entityClass));

        if (!$supports) {
            return false;
        }

        $this->ids[$this->getIdStorageKey($request, $argument->getName())] = $id;

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function resolve(Request $request, ArgumentMetadata $argument)
    {
        $entityClass = $argument->getType();
        $id = $this->ids[$this->getIdStorageKey($request, $argument->getName())];

        $entity = $this->entityManager->find($entityClass, $id);
        if (null === $entity && !$argument->isNullable()) {
            throw new ResourceNotFoundException($entityClass, $id);
        }

        yield $entity;
    }

    /**
     * @param Request $request
     * @param string  $entityClass
     *
     * @return array|null
     */
    private function extractIdFromRequest(Request $request, string $entityClass): ?array
    {
        $idFields = $this->entityManager->getClassMetadata($entityClass)->getIdentifierFieldNames();
        $entityClassShortName = \lcfirst(\substr($entityClass, \strrpos($entityClass, '\\') + 1));
        $id = [];

        foreach ($idFields as $idField) {
            $possibleKeys = [$entityClassShortName.\ucfirst($idField), $idField];

            foreach ($possibleKeys as $possibleKey) {
                if ($request->attributes->has($possibleKey)) {
                    $id[$idField] = $request->attributes->get($possibleKey);

                    break;
                }
            }

            if (!\array_key_exists($idField, $id)) {
                return null;
            }
        }

        return $id;
    }

    /**
     * @param Request $request
     * @param string  $argumentName
     *
     * @return string
     */
    private function getIdStorageKey(Request $request, string $argumentName): string
    {
        return \spl_object_hash($request).'_'.$argumentName;
    }
}
