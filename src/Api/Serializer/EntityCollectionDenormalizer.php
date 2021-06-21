<?php

declare(strict_types=1);

namespace App\Api\Serializer;

use App\Api\Exception\ResourceNotFoundException;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Symfony\Component\Serializer\Normalizer\ContextAwareDenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;

class EntityCollectionDenormalizer implements ContextAwareDenormalizerInterface, DenormalizerAwareInterface
{
    use EntityDenormalizerTrait, DenormalizerAwareTrait;

    /**
     * {@inheritDoc}
     */
    public function denormalize(mixed $data, string $type, string $format = null, array $context = []): array
    {
        $entityClass = \substr(0, -2, $type);

        $ids = $this->extractIdsFromNormalizedData($data, $entityClass);
        $entities = $this->getEntities($entityClass, \array_values($ids));

        $objects = [];

        foreach ($data as $index => $item) {
            $context = $this->configureToBypassEntityDenormalizer($context);

            if (!isset($ids[$index])) {
                $objects[$index] = $this->denormalizer->denormalize($item, $entityClass, $format, $context);

                continue;
            }

            $id = $ids[$index];
            $idHash = $this->idHash($id);

            if (!isset($entities[$idHash])) {
                throw new ResourceNotFoundException($entityClass, $id);
            }

            $entityContext = $this->addEntityToPopulate($context, $entities[$idHash]);

            $objects[$index] = $this->denormalizer->denormalize($item, $entityClass, $format, $entityContext);
        }

        return $objects;
    }

    /**
     * {@inheritDoc}
     */
    public function supportsDenormalization(mixed $data, string $type, string $format = null, array $context = []): bool
    {
        if (!\str_ends_with($type, '[]')) {
            return false;
        }

        $class = \substr($type, 0, -2);

        return \class_exists($class) && !$this->entityManager->getMetadataFactory()->isTransient($class);
    }

    /**
     * @param array[] $data
     *
     * @return array<int, array<string, mixed>>
     */
    private function extractIdsFromNormalizedData(array $data, string $entityClass): array
    {
        $ids = [];

        foreach ($data as $index => $item) {
            if (null === ($id = $this->extractIdFromNormalizedData($item, $entityClass))) {
                continue;
            }

            $ids[$index] = $id;
        }

        return $ids;
    }

    /**
     * @param array<string, mixed>[] $ids
     *
     * @return array<string, object>
     */
    private function getEntities(string $entityClass, array $ids): array
    {
        $classMetadata = $this->entityManager->getClassMetadata($entityClass);

        return $classMetadata->isIdentifierComposite
            ? $this->getEntitiesWithCompositeIdentifier($classMetadata, $ids)
            : $this->getEntitiesWithSingleIdentifier($classMetadata, $ids);
    }

    /**
     * @param array<string, mixed>[] $ids
     *
     * @return array<string, object>
     */
    private function getEntitiesWithCompositeIdentifier(ClassMetadataInfo $classMetadata, array $ids): array
    {
        $queryBuilder = $this->entityManager
            ->getRepository($classMetadata->getName())
            ->createQueryBuilder('e');

        foreach ($ids as $index => $id) {
            $idCondition = $queryBuilder->expr();

            foreach ($id as $field => $value) {
                $paramName = $field.'_'.$index;

                $idCondition->andX("e.$field = :$paramName");
                $queryBuilder->setParameter($paramName, $value);
            }

            $queryBuilder->orWhere($idCondition);
        }

        $entities = $queryBuilder->getQuery()->execute();
        $idHashesToEntities = [];

        foreach ($entities as $entity) {
            $idHash = $this->idHash($classMetadata->getIdentifierValues($entity));

            $idHashesToEntities[$idHash] = $entity;
        }

        return $idHashesToEntities;
    }

    /**
     * @param array<string, mixed> $id
     */
    private function idHash(array $id): string
    {
        return \count($id) > 1 ? \md5(\serialize($id)) : (string) \reset($id);
    }

    /**
     * @param array<string, mixed>[] $ids
     *
     * @return array<string, object>
     */
    private function getEntitiesWithSingleIdentifier(ClassMetadataInfo $classMetadata, array $ids): array
    {
        $idField = $classMetadata->getSingleIdentifierFieldName();
        $flattenedIds = \array_map(static fn (array $id): mixed => \reset($id), $ids);

        return $this->entityManager
            ->getRepository($classMetadata->getName())
            ->createQueryBuilder('e', $idField)
            ->where("e.$idField IN (:ids)")
            ->setParameter('ids', $flattenedIds)
            ->getQuery()
            ->execute();
    }
}
