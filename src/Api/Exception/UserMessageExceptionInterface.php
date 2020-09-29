<?php

declare(strict_types=1);

namespace App\Api\Exception;

/**
 * Interface to be implemented by exceptions which expose a specific message and code to API users.
 */
interface UserMessageExceptionInterface
{
    /**
     * @return string
     */
    public function getUserMessage(): string;

    /**
     * @return int
     */
    public function getUserCode(): int;
}
