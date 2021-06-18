<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use App\Core\Http\SessionListener;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\EventListener\ErrorListener;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\Storage\MetadataBag;
use Symfony\Component\HttpFoundation\Session\Storage\MockFileSessionStorage;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use Symfony\Component\HttpFoundation\Session\Storage\SessionStorageInterface;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver\DefaultValueResolver;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver\RequestAttributeValueResolver;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver\RequestValueResolver;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver\ServiceValueResolver;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver\SessionValueResolver;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver\VariadicValueResolver;
use Symfony\Component\HttpKernel\Controller\ArgumentResolverInterface;
use Symfony\Component\HttpKernel\Controller\ContainerControllerResolver;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;
use Symfony\Component\HttpKernel\EventListener\DisallowRobotsIndexingListener;
use Symfony\Component\HttpKernel\EventListener\ResponseListener;
use Symfony\Component\HttpKernel\EventListener\StreamedResponseListener;
use Symfony\Component\HttpKernel\EventListener\ValidateRequestListener;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Controller\UserValueResolver;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set(ContainerControllerResolver::class)
        ->args([service('service_container')]);

    $services->alias(ControllerResolverInterface::class, ContainerControllerResolver::class);

    $services->set(RequestStack::class);

    $services->set(ArgumentResolver::class)
        ->args([null, abstract_arg('argument value resolvers')]);

    $services->alias(ArgumentResolverInterface::class, ArgumentResolver::class);

    // Argument value resolvers.
    $services->set(DefaultValueResolver::class)
        ->tag('controller.argument_value_resolver', ['priority' => -32]);

    $services->set(RequestAttributeValueResolver::class)
        ->tag('controller.argument_value_resolver', ['priority' => 32]);

    $services->set(RequestValueResolver::class)
        ->tag('controller.argument_value_resolver', ['priority' => 16]);

    $services->set(ServiceValueResolver::class)
        ->args([abstract_arg('container')])
        ->tag('controller.argument_value_resolver', ['priority' => -16]);

    $services->set(SessionValueResolver::class)
        ->tag('controller.argument_value_resolver', ['priority' => 16]);

    $services->set(VariadicValueResolver::class)
        ->tag('controller.argument_value_resolver', ['priority' => -64]);

    $services->set(UserValueResolver::class)
        ->args([service(TokenStorageInterface::class)])
        ->tag('controller.argument_value_resolver', ['priority' => 16]);
    // End of - Argument value resolvers.

    $services->set(HttpKernel::class)
        ->args([
            service(EventDispatcherInterface::class),
            service(ControllerResolverInterface::class),
            service(RequestStack::class),
            service(ArgumentResolverInterface::class),
        ]);

    $services->alias(HttpKernelInterface::class, HttpKernel::class)
        ->public();

    // Listeners.
    $services->set(SessionListener::class)
        ->args([service_locator([
            SessionInterface::class => service(SessionInterface::class),
            SessionStorageInterface::class => service(SessionStorageInterface::class),
            RequestStack::class => service(RequestStack::class),
        ])])
        ->tag('kernel.event_subscriber');

    $services->set(DisallowRobotsIndexingListener::class)
        ->tag('kernel.event_subscriber');

    $services->set(ErrorListener::class)
        ->args([abstract_arg('controller'), service(LoggerInterface::class), param('kernel.debug')])
        ->tag('kernel.event_subscriber');

    $services->set(ResponseListener::class)
        ->args([param('kernel.charset')])
        ->tag('kernel.event_subscriber');

    $services->set(StreamedResponseListener::class)
        ->tag('kernel.event_subscriber');

    $services->set(ValidateRequestListener::class)
        ->tag('kernel.event_subscriber');
    // End of - Listeners.

    // Session.
    $services->set(MetadataBag::class)
        ->args(['_app_meta']);

    $services->set(NativeSessionStorage::class)
        ->args([abstract_arg('options'), abstract_arg('handler'), service(MetadataBag::class)]);

    $services->set(MockFileSessionStorage::class)
        ->args([null, 'MOCKSESSID', service(MetadataBag::class)]);

    $services->set(AttributeBag::class)
        ->args(['_app_attributes']);

    $services->alias(AttributeBagInterface::class, AttributeBag::class);

    $services->set(FlashBag::class)
        ->args(['_app_flashes']);

    $services->alias(FlashBagInterface::class, FlashBag::class);

    $services->set(Session::class)
        ->args([
            service(SessionStorageInterface::class),
            service(AttributeBagInterface::class),
            service(FlashBagInterface::class),
        ]);

    $services->alias(SessionInterface::class, Session::class);
    // End of - Session.
};
