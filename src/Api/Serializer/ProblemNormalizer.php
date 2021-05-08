<?php

declare(strict_types=1);

namespace App\Api\Serializer;

use App\Api\Error\Problem;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProblemNormalizer implements NormalizerInterface, NormalizerAwareInterface, CacheableSupportsMethodInterface
{
    use NormalizerAwareTrait;

    private bool $debug;

    /**
     * @param bool $debug
     */
    public function __construct(bool $debug)
    {
        $this->debug = $debug;
    }

    /**
     * {@inheritDoc}
     */
    public function normalize($object, string $format = null, array $context = []): array
    {
        /** @var Problem $object */
        $data = [
            'title' => $object->getTitle(),
            'code' => $object->getCode(),
            'status' => $object->getStatus(),
            'detail' => $object->getDetail(),
        ];

        if ($object->hasData()) {
            $data = \array_merge($data, $this->normalizer->normalize($object->getData(), $format, $context));
        }

        if ($this->debug && $object->hasException()) {
            $flattenException = $object->getFlattenException();

            $data['debug'] = [
                'message' => $flattenException->getMessage(),
                'class' => $flattenException->getClass(),
                'trace' => $flattenException->getTrace(),
            ];
        }

        return $data;
    }

    /**
     * {@inheritDoc}
     */
    public function supportsNormalization($data, string $format = null): bool
    {
        return $data instanceof Problem;
    }

    /**
     * {@inheritDoc}
     */
    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
