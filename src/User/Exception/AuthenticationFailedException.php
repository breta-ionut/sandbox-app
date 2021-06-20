<?php

declare(strict_types=1);

namespace App\User\Exception;

use App\Api\Exception\NoCustomHeadersHttpExceptionTrait;
use App\Api\Exception\UserMessageExceptionInterface;
use App\User\Error\UserCodes;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class AuthenticationFailedException
    extends AuthenticationException
    implements HttpExceptionInterface, UserMessageExceptionInterface
{
    use NoCustomHeadersHttpExceptionTrait;

    public function __construct(int $code = 0, \Throwable $previous = null)
    {
        parent::__construct('Authentication failed.', $code, $previous);
    }

    /**
     * {@inheritDoc}
     */
    public function getStatusCode(): int
    {
        return Response::HTTP_UNAUTHORIZED;
    }

    public function getUserMessage(): string
    {
        return 'Authentication failed.';
    }

    public function getUserCode(): int
    {
        return UserCodes::AUTHENTICATION_FAILED;
    }
}
