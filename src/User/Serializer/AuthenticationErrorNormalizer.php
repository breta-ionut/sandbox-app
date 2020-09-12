<?php

declare(strict_types=1);

namespace App\User\Serializer;

use App\Api\Serializer\ExceptionNormalizer;
use App\User\Error\UserCodes;
use App\User\Model\AuthenticationError;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AuthenticationErrorNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    private const TITLE = 'Authentication failed.';

    private ExceptionNormalizer $exceptionNormalizer;

    /**
     * @param ExceptionNormalizer $exceptionNormalizer
     */
    public function __construct(ExceptionNormalizer $exceptionNormalizer)
    {
        $this->exceptionNormalizer = $exceptionNormalizer;
    }

    /**
     * {@inheritDoc}
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        $data = [
            'title' => self::TITLE,
            'code' => UserCodes::AUTHENTICATION_FAILED,
            'status' => Response::HTTP_UNAUTHORIZED,
            'detail' => Response::$statusTexts[Response::HTTP_UNAUTHORIZED],
        ];

        /** @var AuthenticationError $object */
        if ($object->hasException()) {
            $data = \array_merge(
                $data,
                $this->exceptionNormalizer->normalize($object->getException(), $format, $context + $data)
            );
        }

        return $data;
    }

    /**
     * {@inheritDoc}
     */
    public function supportsNormalization($data, string $format = null)
    {
        return $data instanceof AuthenticationError;
    }

    /**
     * {@inheritDoc}
     */
    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
