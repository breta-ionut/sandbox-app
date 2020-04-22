<?php

declare(strict_types=1);

namespace App\Core\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

interface WatchdogTokenInterface extends TokenInterface
{
}
