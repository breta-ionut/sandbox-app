<?php

declare(strict_types=1);

namespace App\User\Security;

use App\Api\Http\ResponseFactory;
use App\User\Error\UserCodes;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface;

class AccessDeniedHandler implements AccessDeniedHandlerInterface
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
    public function handle(Request $request, AccessDeniedException $accessDeniedException)
    {
        $context = [
            'title' => 'Access denied.',
            'code' => UserCodes::ACCESS_DENIED,
            'status' => Response::HTTP_FORBIDDEN,
            'detail' => Response::$statusTexts[Response::HTTP_FORBIDDEN],
        ];

        return $this->responseFactory->createFromThrowable(
            $accessDeniedException,
            Response::HTTP_FORBIDDEN,
            [],
            $context
        );
    }
}
