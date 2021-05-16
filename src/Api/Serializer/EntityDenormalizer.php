<?php

declare(strict_types=1);

namespace App\Api\Serializer;

use App\Api\Exception\ResourceNotFoundException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ContextAwareDenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;

class EntityDenormalizer implements ContextAwareDenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    private EntityManagerInterface $entityManager;

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
    public function denormalize(mixed $data, string $type, string $format = null, array $context = []): object
    {
        $context['bypass_entity_denormalizer'] = true;

        if (null === ($id = $this->extractIdFromNormalizedData($data, $type))) {
            return $this->denormalizer->denormalize($data, $type, $format, $context);
        }

        if (null === ($entity = $this->entityManager->find($type, $id))) {
            throw new ResourceNotFoundException($type, $id);
        }

        $context[AbstractNormalizer::OBJECT_TO_POPULATE] = $entity;

        return $this->denormalizer->denormalize($data, $type, $format, $context);
    }

    /**
     * {@inheritDoc}
     */
    public function supportsDenormalization(mixed $data, string $type, string $format = null, array $context = []): bool
    {
        return empty($context['bypass_entity_denormalizer'])
            && \class_exists($type)
            && !$this->entityManager->getMetadataFactory()->isTransient($type);
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
