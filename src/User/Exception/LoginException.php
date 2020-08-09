<?php

declare(strict_types=1);

namespace App\User\Exception;

use App\Api\Exception\UserMessageExceptionInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class LoginException extends \RuntimeException implements HttpExceptionInterface, UserMessageExceptionInterface
{
    /**
     * @param int             $code
     * @param \Throwable|null $previous
     */
    public function __construct(int $code = 0, \Throwable $previous = null)
    {
        $message = 'Authentication failed.';

        parent::__construct($message, $code, $previous);
    }

    /**
     * {@inheritDoc}
     */
    public function getUserMessage(): string
    {
        return 'Authentication failed.';
    }

    /**
     * {@inheritDoc}
     */
    public function getUserCode(): int
    {
        return 200;
    }

    /**
     * {@inheritDoc}
     */
    public function getStatusCode()
    {
        return Response::HTTP_FORBIDDEN;
    }

    /**
     * {@inheritDoc}
     */
    public function getHeaders()
    {
        return [];
    }
}
