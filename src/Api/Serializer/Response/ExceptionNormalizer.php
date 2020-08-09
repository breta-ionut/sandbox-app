<?php

declare(strict_types=1);

namespace App\Api\Serializer\Response;

use App\Api\Exception\UserMessageExceptionInterface;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;

class ExceptionNormalizer implements ContextAwareNormalizerInterface
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
        if ($object instanceof UserMessageExceptionInterface) {
            $data = [
                'title' => $object->getUserMessage(),
                'code' => $object->getUserCode(),
            ];
        } else {
            $data = self::UNKNOWN_ERROR_DATA;
        }

        $flattenException = FlattenException::createFromThrowable($object);

        $data += [
            'status' => $flattenException->getStatusCode(),
            'detail' => $this->debug ? $flattenException->getMessage() : $flattenException->getStatusText(),
        ];
        if ($this->debug) {
            $data += [
                'class' => $flattenException->getClass(),
                'trace' => $flattenException->getTrace(),
            ];
        }

        return $data;
    }

    /**
     * {@inheritDoc}
     */
    public function supportsNormalization($data, string $format = null, array $context = [])
    {
        return $data instanceof \Throwable && !empty($context['api_response']);
    }
}
