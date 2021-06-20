<?php

declare(strict_types=1);

namespace App\Api\Exception;

use App\Api\Error\UserCodes;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class ValidationException
    extends \RuntimeException
    implements HttpExceptionInterface, UserMessageExceptionInterface, UserDataExceptionInterface
{
    use NoCustomHeadersHttpExceptionTrait;

    public function __construct(
        private ConstraintViolationListInterface $violations,
        int $code = 0,
        \Throwable $previous = null,
    ) {
        $message = 'Validation failed.';

        parent::__construct($message, $code, $previous);
    }

    /**
     * {@inheritDoc}
     */
    public function getStatusCode(): int
    {
        return Response::HTTP_BAD_REQUEST;
    }

    public function getUserMessage(): string
    {
        return 'Validation failed.';
    }

    public function getUserCode(): int
    {
        return UserCodes::VALIDATION;
    }

    public function getUserData(): ConstraintViolationListInterface
    {
        return $this->violations;
    }
}
