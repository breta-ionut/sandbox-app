<?php

declare(strict_types=1);

namespace App\User\Security;

use App\Api\Error\Problem;
use App\Api\Http\ResponseFactory;
use App\User\Error\UserCodes;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface;

class AccessDeniedHandler implements AccessDeniedHandlerInterface
{
    public function __construct(private ResponseFactory $responseFactory)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function handle(Request $request, AccessDeniedException $accessDeniedException): JsonResponse
    {
        $problem = (new Problem())
            ->setTitle('Access denied.')
            ->setCode(UserCodes::ACCESS_DENIED)
            ->setStatus(Response::HTTP_FORBIDDEN)
            ->fromException($accessDeniedException);

        return $this->responseFactory->createFromProblem($problem);
    }
}
