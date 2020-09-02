<?php

declare(strict_types=1);

namespace App\Api\Exception;

use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

/**
 * Trait to be used by {@see HttpExceptionInterface} implementations which don't expose any custom headers.
 */
trait NoCustomHeadersHttpExceptionTrait
{
    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return [];
    }
}
