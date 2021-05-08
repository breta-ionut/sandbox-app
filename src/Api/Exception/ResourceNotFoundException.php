<?php

declare(strict_types=1);

namespace App\Api\Exception;

use App\Api\Error\UserCodes;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ResourceNotFoundException extends \RangeException implements HttpExceptionInterface, UserMessageExceptionInterface
{
    use NoCustomHeadersHttpExceptionTrait;

    /**
     * @param string          $class
     * @param mixed           $id
     * @param int             $code
     * @param \Throwable|null $previous
     */
    public function __construct(string $class, mixed $id, int $code = 0, \Throwable $previous = null)
    {
        $message = \sprintf('Resource of type "%s" (identified by %s) could not be found.', $class, \json_encode($id));

        parent::__construct($message, $code, $previous);
    }

    /**
     * {@inheritDoc}
     */
    public function getStatusCode(): int
    {
        return Response::HTTP_NOT_FOUND;
    }

    /**
     * {@inheritDoc}
     */
    public function getUserMessage(): string
    {
        return 'Resource not found.';
    }

    /**
     * {@inheritDoc}
     */
    public function getUserCode(): int
    {
        return UserCodes::RESOURCE_NOT_FOUND;
    }
}
