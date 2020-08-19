<?php

declare(strict_types=1);

namespace App\Api\Serializer\Response;

use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;

class ExceptionNormalizer implements ContextAwareNormalizerInterface
{
    use ExceptionNormalizerTrait;

    /**
     * {@inheritDoc}
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        return $this->normalizeException($object);
    }

    /**
     * {@inheritDoc}
     */
    public function supportsNormalization($data, string $format = null, array $context = [])
    {
        return $data instanceof \Throwable && $this->isNormalizationForApiResponseRequired($context);
    }
}
