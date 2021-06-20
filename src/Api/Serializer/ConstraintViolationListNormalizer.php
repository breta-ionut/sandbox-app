<?php

declare(strict_types=1);

namespace App\Api\Serializer;

use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\ConstraintViolationListNormalizer as BaseConstraintViolationListNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ConstraintViolationListNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    public function __construct(private BaseConstraintViolationListNormalizer $decorated)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function normalize($object, string $format = null, array $context = []): array
    {
        $data = $this->decorated->normalize($object, $format, $context);

        return ['detail' => $data['detail'], 'violations' => $data['violations']];
    }

    /**
     * {@inheritDoc}
     */
    public function supportsNormalization($data, string $format = null): bool
    {
        return $this->decorated->supportsNormalization($data, $format);
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return $this->decorated->hasCacheableSupportsMethod();
    }
}
