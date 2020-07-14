<?php

declare(strict_types=1);

namespace App\Api\Http;

use Symfony\Component\HttpFoundation\JsonResponse;

trait ResponseTrait
{
    /**
     * @param int    $httpCode
     * @param int    $code
     * @param string $message
     *
     * @return JsonResponse
     */
    protected function createErrorResponse(int $httpCode, int $code, string $message): JsonResponse
    {
        return new JsonResponse(['code' => $code, 'message' => $message], $httpCode);
    }
}
