<?php

declare(strict_types=1);

namespace App\Api\Serializer;

use App\Api\Exception\ValidationException;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\ConstraintViolationListNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ValidationExceptionNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    private ExceptionNormalizer $exceptionNormalizer;
    private ConstraintViolationListNormalizer $violationsNormalizer;

    /**
     * @param ExceptionNormalizer               $exceptionNormalizer
     * @param ConstraintViolationListNormalizer $violationsNormalizer
     */
    public function __construct(
        ExceptionNormalizer $exceptionNormalizer,
        ConstraintViolationListNormalizer $violationsNormalizer
    ) {
        $this->exceptionNormalizer = $exceptionNormalizer;
        $this->violationsNormalizer = $violationsNormalizer;
    }

    /**
     * {@inheritDoc}
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        $violationsData = $this->violationsNormalizer->normalize($object->getViolations(), $format, $context);

        return $this->exceptionNormalizer->normalize(
            $object,
            $format,
            $context + ['detail' => $violationsData['detail']]
        ) + ['violations' => $violationsData['violations']];
    }

    /**
     * {@inheritDoc}
     */
    public function supportsNormalization($data, string $format = null)
    {
        return $data instanceof ValidationException;
    }

    /**
     * {@inheritDoc}
     */
    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
