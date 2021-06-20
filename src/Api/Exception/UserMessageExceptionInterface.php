<?php

declare(strict_types=1);

namespace App\Api\Exception;

/**
 * Interface to be implemented by exceptions which expose a specific message and code to API users.
 */
interface UserMessageExceptionInterface
{
    public function getUserMessage(): string;

    public function getUserCode(): int;
}
