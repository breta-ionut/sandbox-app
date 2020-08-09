<?php

declare(strict_types=1);

namespace App\Api\Exception;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class MalformedInputException extends \RuntimeException implements HttpExceptionInterface, UserMessageExceptionInterface
{
    /**
     * @param int             $code
     * @param \Throwable|null $previous
     */
    public function __construct(int $code = 0, \Throwable $previous = null)
    {
        $message = 'Bad JSON or with invalid fields sent as input.';

        parent::__construct($message, $code, $previous);
    }

    /**
     * {@inheritDoc}
     */
    public function getStatusCode()
    {
        return Response::HTTP_BAD_REQUEST;
    }

    /**
     * {@inheritDoc}
     */
    public function getHeaders()
    {
        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function getUserMessage(): string
    {
        return 'Malformed input sent.';
    }

    /**
     * {@inheritDoc}
     */
    public function getUserCode(): int
    {
        return 100;
    }
}
