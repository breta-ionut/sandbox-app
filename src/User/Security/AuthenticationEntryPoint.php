<?php

declare(strict_types=1);

namespace App\User\Security;

use App\Api\Error\Problem;
use App\Api\Http\ResponseFactory;
use App\User\Error\UserCodes;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

class AuthenticationEntryPoint implements AuthenticationEntryPointInterface
{
    public function __construct(private ResponseFactory $responseFactory)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function start(Request $request, AuthenticationException $authException = null): JsonResponse
    {
        $problem = (new Problem())
            ->setTitle('Authentication required.')
            ->setCode(UserCodes::AUTHENTICATION_REQUIRED)
            ->setStatus(Response::HTTP_UNAUTHORIZED);

        if (null !== $authException) {
            $problem->fromException($authException);
        }

        return $this->responseFactory->createFromProblem($problem);
    }
}
