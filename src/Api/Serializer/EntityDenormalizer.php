<?php

declare(strict_types=1);

namespace App\Api\Serializer;

use App\Api\Exception\ResourceNotFoundException;
use Symfony\Component\Serializer\Normalizer\ContextAwareDenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;

class EntityDenormalizer implements ContextAwareDenormalizerInterface, DenormalizerAwareInterface
{
    use EntityDenormalizerTrait, DenormalizerAwareTrait;

    /**
     * {@inheritDoc}
     */
    public function denormalize(mixed $data, string $type, string $format = null, array $context = []): object
    {
        $context = $this->addConfigurationToBypassEntityDenormalizer($context, $data);

        if (null === ($id = $this->extractIdFromNormalizedData($data, $type))) {
            return $this->denormalizer->denormalize($data, $type, $format, $context);
        }

        if (null === ($entity = $this->entityManager->find($type, $id))) {
            throw new ResourceNotFoundException($type, $id);
        }

        return $this->denormalizer->denormalize($data, $type, $format, $this->addEntityToPopulate($context, $entity));
    }

    /**
     * {@inheritDoc}
     */
    public function supportsDenormalization(mixed $data, string $type, string $format = null, array $context = []): bool
    {
        return \is_array($data)
            && \class_exists($type)
            && !$this->entityManager->getMetadataFactory()->isTransient($type)
            && $this->shouldApplyEntityDenormalizer($data, $context);
    }
}
