<?php

declare(strict_types=1);

namespace App\Core\Routing;

use Symfony\Component\Routing\Loader\AnnotationClassLoader;
use Symfony\Component\Routing\Route;

class AnnotationControllerLoader extends AnnotationClassLoader
{
    /**
     * {@inheritDoc}
     */
    protected function configureRoute(
        Route $route,
        \ReflectionClass $class,
        \ReflectionMethod $method,
        object $annot,
    ): void {
        if ('__invoke' === $method->getName()) {
            $controller = $class->getName();
        } else {
            $controller = \sprintf('%s::%s', $class->getName(), $method->getName());
        }

        $route->setDefault('_controller', $controller);
    }
}
