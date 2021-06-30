<?php

declare(strict_types=1);

namespace App\Api\Serializer;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

trait EntityDenormalizerTrait
{
    private EntityManagerInterface $entityManager;

    /**
     * @required
     */
    public function setEntityManager(EntityManagerInterface $entityManager): void
    {
        $this->entityManager = $entityManager;
    }

    private function shouldApplyEntityDenormalizer(mixed $data, array $context): bool
    {
        return empty($context['bypass_entity_denormalizer'])
            || $this->dataHash($data) !== $context['bypass_entity_denormalizer'];
    }

    private function dataHash(mixed $data): string
    {
        return \md5(\serialize($data));
    }

    private function addConfigurationToBypassEntityDenormalizer(array $context, mixed $data): array
    {
        $context['bypass_entity_denormalizer'] = $this->dataHash($data);

        return $context;
    }

    private function extractIdFromNormalizedData(mixed $data, string $entityClass): ?array
    {
        if (!\is_array($data)) {
            return null;
        }

        $idFields = $this->entityManager->getClassMetadata($entityClass)->getIdentifierFieldNames();
        $id = [];

        foreach ($idFields as $idField) {
            if (!\array_key_exists($idField, $data)) {
                return null;
            }

            $id[$idField] = $data[$idField];
        }

        return $id;
    }

    private function addEntityToPopulate(array $context, object $entity): array
    {
        $context[AbstractNormalizer::OBJECT_TO_POPULATE] = $entity;

        return $context;
    }
}
