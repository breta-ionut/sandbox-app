<?php

declare(strict_types=1);

namespace App\Core\Routing;

use App\Core\Controller\RouterRedirectController;
use Symfony\Component\Routing\Matcher\CompiledUrlMatcher;
use Symfony\Component\Routing\Matcher\RedirectableUrlMatcherInterface;

class RedirectableCompiledUrlMatcher extends CompiledUrlMatcher implements RedirectableUrlMatcherInterface
{
    /**
     * {@inheritDoc}
     */
    public function redirect(string $path, string $route, string $scheme = null): array
    {
        return [
            'path' => $path,
            'scheme' => $scheme,
            'httpPort' => $this->context->getHttpPort(),
            'httpsPort' => $this->context->getHttpsPort(),
            '_route' => $route,
            '_controller' => RouterRedirectController::class,
        ];
    }
}
