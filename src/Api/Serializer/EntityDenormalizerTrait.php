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

    private function shouldApplyEntityDenormalizer(array $context): bool
    {
        if (empty($context['bypass_entity_denormalizer'])) {
            return true;
        }

        // Reset the flag to its initial value to avoid a second bypassing. The flag is passed as a reference, so this
        // change will be reflected outside the current scope.
        /** @noinspection PhpArrayWriteIsNotUsedInspection */
        $context['bypass_entity_denormalizer'] = false;

        return false;
    }

    private function configureToBypassEntityDenormalizer(array $context): array
    {
        // By setting the flag as a reference to a static variable, its value becomes alterable from other scopes
        // without needing to pass the context by reference. The bypassing of the entity denormalizer should be done
        // only once upon the call of this function, therefore it is a need to have the value of the flag reset after
        // the bypass.
        /** @noinspection PhpUnusedLocalVariableInspection */
        static $bypassEntityDenormalizer;

        $bypassEntityDenormalizer = true;
        $context['bypass_entity_denormalizer'] =& $bypassEntityDenormalizer;

        return $context;
    }

    private function addEntityToPopulate(array $context, object $entity): array
    {
        $context[AbstractNormalizer::OBJECT_TO_POPULATE] = $entity;

        return $context;
    }

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
