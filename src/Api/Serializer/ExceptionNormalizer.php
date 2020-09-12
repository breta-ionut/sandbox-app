<?php

declare(strict_types=1);

namespace App\Api\Serializer;

use App\Api\Exception\UserMessageExceptionInterface;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ExceptionNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    private const UNKNOWN_ERROR_DATA = [
        'title' => 'An error occurred.',
        'code' => 999,
    ];

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
    public function normalize($object, string $format = null, array $context = [])
    {
        $data = [];

        $this->setPropertiesProvidedByContext($data, $context);
        $this->ensureTitleAndCode($data, $object);
        $this->ensureStatusDetailAndDebugData($data, $object);

        return $data;
    }

    /**
     * {@inheritDoc}
     */
    public function supportsNormalization($data, string $format = null)
    {
        return $data instanceof \Throwable;
    }

    /**
     * {@inheritDoc}
     */
    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }

    /**
     * @param array $data
     * @param array $context
     */
    private function setPropertiesProvidedByContext(array &$data, array $context): void
    {
        foreach (['title', 'code', 'status', 'detail'] as $property) {
            if (isset($context[$property])) {
                $data[$property] = $context[$property];
            }
        }
    }

    /**
     * @param array      $data
     * @param \Throwable $exception
     */
    private function ensureTitleAndCode(array &$data, \Throwable $exception): void
    {
        if (isset($data['title'], $data['code'])) {
            return;
        }

        if ($exception instanceof UserMessageExceptionInterface) {
            $data += ['title' => $exception->getUserMessage(), 'code' => $exception->getUserCode()];
        } else {
            $data += self::UNKNOWN_ERROR_DATA;
        }
    }

    /**
     * @param array      $data
     * @param \Throwable $exception
     */
    private function ensureStatusDetailAndDebugData(array &$data, \Throwable $exception): void
    {
        if (isset($data['status'], $data['detail']) && !$this->debug) {
            return;
        }

        $flattenException = FlattenException::createFromThrowable($exception);

        $data += ['status' => $flattenException->getStatusCode(), 'detail' => $flattenException->getStatusText()];

        if ($this->debug) {
            $data = \array_merge($data, [
                'detail' => $flattenException->getMessage(),
                'class' => $flattenException->getClass(),
                'trace' => $flattenException->getTrace(),
            ]);
        }
    }
}
