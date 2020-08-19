<?php

declare(strict_types=1);

namespace App\Api\Serializer\Response;

use App\Api\Exception\ValidationException;
use Symfony\Component\Serializer\Normalizer\ConstraintViolationListNormalizer;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;

class ValidationExceptionNormalizer implements ContextAwareNormalizerInterface
{
    use ExceptionNormalizerTrait;

    private ConstraintViolationListNormalizer $violationsNormalizer;

    /**
     * @param ConstraintViolationListNormalizer $violationsNormalizer
     */
    public function __construct(ConstraintViolationListNormalizer $violationsNormalizer)
    {
        $this->violationsNormalizer = $violationsNormalizer;
    }

    /**
     * {@inheritDoc}
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        /** @var ValidationException $object */
        $violationsData = $this->violationsNormalizer->normalize(
            $object->getViolations(),
            $format,
            $context
        )['violations'];

        return $this->normalizeException($object) + ['violations' => $violationsData];
    }

    /**
     * {@inheritDoc}
     */
    public function supportsNormalization($data, string $format = null, array $context = [])
    {
        return $data instanceof ValidationException && $this->isNormalizationForApiResponseRequired($context);
    }
}
