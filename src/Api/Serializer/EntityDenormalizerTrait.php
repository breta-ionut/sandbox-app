<?php

declare(strict_types=1);

namespace App\Api\Serializer;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

trait EntityDenormalizerTrait
{
    private EntityManagerInterface $entityManager;

    /**
     * @param EntityManagerInterface $entityManager
     *
     * @required
     */
    public function setEntityManager(EntityManagerInterface $entityManager): void
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param array $context
     *
     * @return bool
     */
    private function shouldApplyEntityDenormalizer(array $context): bool
    {
        return empty($context['bypass_entity_denormalizer']);
    }

    /**
     * @param array $context
     *
     * @return array
     */
    private function configureToBypassEntityDenormalizer(array $context): array
    {
        $context['bypass_entity_denormalizer'] = true;

        return $context;
    }

    /**
     * @param array  $context
     * @param object $object
     *
     * @return array
     */
    private function addObjectToPopulate(array $context, object $object): array
    {
        $context[AbstractNormalizer::OBJECT_TO_POPULATE] = $object;

        return $context;
    }

    /**
     * @param array  $data
     * @param string $entityClass
     *
     * @return array|null
     */
    private function extractIdFromNormalizedData(array $data, string $entityClass): ?array
    {
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
}
