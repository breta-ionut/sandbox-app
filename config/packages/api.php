<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use App\Api\Controller\ErrorController;
use App\Api\Http\Controller\EntityValueResolver;
use App\Api\Http\Controller\InputObjectValueResolver;
use App\Api\Http\Listener\ConfigureApiEndpointsListener;
use App\Api\Http\Listener\ExceptionListener;
use App\Api\Serializer\ConstraintViolationListNormalizer;
use App\Api\Serializer\ProblemNormalizer;
use Symfony\Component\HttpKernel\EventListener\ErrorListener;
use Symfony\Component\Serializer\Normalizer\ConstraintViolationListNormalizer as BaseConstraintViolationListNormalizer;

return static function (ContainerConfigurator $container): void {
    $container->parameters()
        ->set('app.api.doc_config_file', param('kernel.config_dir').'/api_doc.yaml')
        ->set('app.api.path_prefix', '/api/');

    $services = $container->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->load('App\\Api\\', '../../src/Api/*')
        ->exclude('../../src/Api/{Error,Exception}');

    $services->load('App\\Api\\Controller\\', '../../src/Api/Controller')
        ->tag('controller.service_arguments');

    $services->set(EntityValueResolver::class)
        ->tag('controller.argument_value_resolver', ['priority' => -4]);

    $services->set(InputObjectValueResolver::class)
        ->tag('controller.argument_value_resolver', ['priority' => -8]);

    $services->set(ConfigureApiEndpointsListener::class)
        ->args([param('app.api.path_prefix')]);

    $services->set('app.api.http.listener.symfony_exception_listener', ErrorListener::class)
        ->args(['$controller' => \sprintf('%s::error', ErrorController::class), '$debug' => param('kernel.debug')]);

    $services->set(ExceptionListener::class)
        ->args([service('app.api.http.listener.symfony_exception_listener')]);

    $services->set(ConstraintViolationListNormalizer::class)
        ->decorate(BaseConstraintViolationListNormalizer::class)
        ->args([service(\sprintf('%s.inner', ConstraintViolationListNormalizer::class))]);

    $services->set(ProblemNormalizer::class)
        ->args([param('kernel.debug')]);
};
