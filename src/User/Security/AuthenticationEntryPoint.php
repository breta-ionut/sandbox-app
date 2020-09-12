<?php

declare(strict_types=1);

namespace App\User\Security;

use App\Api\Http\ResponseFactory;
use App\User\Model\AuthenticationError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

class AuthenticationEntryPoint implements AuthenticationEntryPointInterface
{
    private ResponseFactory $responseFactory;

    /**
     * @param ResponseFactory $responseFactory
     */
    public function __construct(ResponseFactory $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        $authenticationError = new AuthenticationError($authException);

        return $this->responseFactory->createFromData(
            $authenticationError,
            Response::HTTP_UNAUTHORIZED,
            ['Content-Type' => 'application/problem+json']
        );
    }
}
