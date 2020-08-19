<?php

declare(strict_types=1);

namespace App\Api\Serializer\Response;

use App\Api\Exception\UserMessageExceptionInterface;
use Symfony\Component\ErrorHandler\Exception\FlattenException;

trait ExceptionNormalizerTrait
{
    /**
     * @var array
     */
    protected static array $unknownErrorData = [
        'title' => 'An error occurred.',
        'code' => 999,
    ];

    /**
     * @var bool
     */
    protected bool $debug;

    /**
     * @param bool $debug
     *
     * @return $this
     *
     * @required
     */
    public function setDebug(bool $debug): self
    {
        $this->debug = $debug;

        return $this;
    }

    /**
     * @param \Throwable $exception
     *
     * @return array
     */
    protected function normalizeException(\Throwable $exception): array
    {
        if ($exception instanceof UserMessageExceptionInterface) {
            $data = [
                'title' => $exception->getUserMessage(),
                'code' => $exception->getUserCode(),
            ];
        } else {
            $data = self::$unknownErrorData;
        }

        $flattenException = FlattenException::createFromThrowable($exception);

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
     * @param array $context
     *
     * @return bool
     */
    protected function isNormalizationForApiResponseRequired(array $context): bool
    {
        return !empty($context['api_response']);
    }
}
