<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use App\User\Model\User;
use App\User\Security\AccessDeniedHandler;
use App\User\Security\AuthenticationEntryPoint;
use App\User\Security\Authenticator\LoginAuthenticator;
use App\User\Security\Authenticator\TokenAuthenticator;
use App\User\Security\UserProvider\LoginUserProvider;
use App\User\Security\UserProvider\TokenUserProvider;
use Symfony\Component\HttpFoundation\Request;

return static function (ContainerConfigurator $container): void {
    $container->extension('security', [
        'password_hashers' => [
            User::class => 'auto',
        ],
        'firewalls' => [
            'frontend' => [
                'path' => '^/(?!api/)',
                'security' => false,
            ],
            'api_doc' => [
                'path' => '^/api/doc',
                'security' => false,
            ],
            'api_login' => [
                'path' => '^/api/user/login$',
                'methods' => Request::METHOD_POST,
                'stateless' => true,
                'entry_point' => AuthenticationEntryPoint::class,
                'access_denied_handler' => AccessDeniedHandler::class,
                'authenticators' => [LoginAuthenticator::class],
                'user_provider' => LoginUserProvider::class,
            ],
            'api' => [
                'path' => '^/api/',
                'stateless' => true,
                'entry_point' => AuthenticationEntryPoint::class,
                'access_denied_handler' => AccessDeniedHandler::class,
                'logout' => [
                    'path' => '/api/user/logout',
                ],
                'authenticators' => [TokenAuthenticator::class],
                'user_provider' => TokenUserProvider::class,
            ],
        ],
        'access_control' => [
            [
                'path' => '^/api/image',
                'roles' => 'PUBLIC_ACCESS',
            ],
            [
                'path' => '^/api/user$',
                'methods' => Request::METHOD_POST,
                'roles' => 'IS_ANONYMOUS',
            ],
            [
                'path' => '^/api',
                'roles' => 'IS_AUTHENTICATED_FULLY',
            ],
        ],
    ]);
};
