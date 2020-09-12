<?php

declare(strict_types=1);

namespace App\Api\Error;

final class UserCodes
{
    public const UNKNOWN_ERROR = 100;
    public const MALFORMED_INPUT = 101;
    public const RESOURCE_NOT_FOUND = 102;
    public const VALIDATION = 103;
}
