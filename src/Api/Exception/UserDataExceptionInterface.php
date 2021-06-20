<?php

declare(strict_types=1);

namespace App\Api\Exception;

/**
 * Interface to be implemented by exceptions which expose additional data to API users.
 */
interface UserDataExceptionInterface
{
    public function getUserData(): mixed;
}
