<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use App\User\Security\LogoutListener;
use App\User\Token\TokenManager;
use App\User\User\UserManager;

return static function (ContainerConfigurator $container): void {
    $container->parameters()
        ->set('app.user.token_availability', 1440); // One day.

    $services = $container->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->load('App\\User\\', '../../src/User/*')
        ->exclude('../../src/User/{Error,Model}');

    $services->load('App\\User\\Controller\\', '../../src/User/Controller')
        ->tag('controller.service_arguments');

    $services->set(LogoutListener::class)
        ->tag('kernel.event_subscriber', ['dispatcher' => 'security.event_dispatcher.api']);

    $services->set(TokenManager::class)
        ->arg('$tokenAvailability', param('app.user.token_availability'));

    $services->set(UserManager::class)
        ->arg('$userAuthenticator', service('security.authenticator_manager.api_login'));
};
