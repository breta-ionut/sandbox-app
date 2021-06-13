<?php

declare(strict_types=1);

namespace App\Api\Controller;

use App\Api\Http\ResponseFactory;
use Symfony\Component\HttpFoundation\JsonResponse;

class ErrorController
{
    public function error(\Throwable $exception, ResponseFactory $responseFactory): JsonResponse
    {
        return $responseFactory->createFromException($exception);
    }
}
