<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactory;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolver;
use Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolverInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManager;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;
use Symfony\Component\Security\Core\Authorization\Voter\RoleVoter;
use Symfony\Component\Security\Csrf\CsrfTokenManager;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\UriSafeTokenGenerator;
use Symfony\Component\Security\Csrf\TokenStorage\ClearableTokenStorageInterface;
use Symfony\Component\Security\Csrf\TokenStorage\SessionTokenStorage;
use Symfony\Component\Security\Csrf\TokenStorage\TokenStorageInterface as CsrfTokenStorageInterface;
use Symfony\Component\Security\Http\AccessMap;
use Symfony\Component\Security\Http\AccessMapInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticatorManager;
use Symfony\Component\Security\Http\Authentication\NoopAuthenticationManager;
use Symfony\Component\Security\Http\EntryPoint\RetryAuthenticationEntryPoint;
use Symfony\Component\Security\Http\EventListener\CheckCredentialsListener;
use Symfony\Component\Security\Http\EventListener\CookieClearingLogoutListener;
use Symfony\Component\Security\Http\EventListener\CsrfProtectionListener;
use Symfony\Component\Security\Http\EventListener\CsrfTokenClearingLogoutListener;
use Symfony\Component\Security\Http\EventListener\DefaultLogoutListener;
use Symfony\Component\Security\Http\EventListener\PasswordMigratingListener;
use Symfony\Component\Security\Http\EventListener\SessionLogoutListener;
use Symfony\Component\Security\Http\EventListener\SessionStrategyListener;
use Symfony\Component\Security\Http\EventListener\UserCheckerListener;
use Symfony\Component\Security\Http\EventListener\UserProviderListener;
use Symfony\Component\Security\Http\Firewall;
use Symfony\Component\Security\Http\Firewall\AccessListener;
use Symfony\Component\Security\Http\Firewall\AuthenticatorManagerListener;
use Symfony\Component\Security\Http\Firewall\ChannelListener;
use Symfony\Component\Security\Http\Firewall\ContextListener;
use Symfony\Component\Security\Http\Firewall\ExceptionListener;
use Symfony\Component\Security\Http\Firewall\LogoutListener;
use Symfony\Component\Security\Http\FirewallMap;
use Symfony\Component\Security\Http\FirewallMapInterface;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Component\Security\Http\Logout\LogoutUrlGenerator;
use Symfony\Component\Security\Http\Session\SessionAuthenticationStrategy;
use Symfony\Component\Security\Http\Session\SessionAuthenticationStrategyInterface;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    // Password hashers.
    $services->set(PasswordHasherFactory::class)
        ->args([abstract_arg('password hashers')]);

    $services->alias(PasswordHasherInterface::class, PasswordHasherFactory::class);

    $services->set(UserPasswordHasher::class)
        ->args([service(PasswordHasherFactoryInterface::class)]);

    $services->alias(UserPasswordHasherInterface::class, UserPasswordHasher::class);
    // End of - Password hashers.

    // Authentication.
    $services->set(AuthenticationTrustResolver::class);
    $services->alias(AuthenticationTrustResolverInterface::class, AuthenticationTrustResolver::class);

    $services->set(TokenStorage::class);
    $services->alias(TokenStorageInterface::class, TokenStorage::class);

    $services->set(AuthenticatorManager::class)
        ->abstract()
        ->args([
            abstract_arg('authenticators'),
            service(TokenStorageInterface::class),
            abstract_arg('event dispatcher'),
            abstract_arg('firewall'),
            service(LoggerInterface::class),
            abstract_arg('erase credentials'),
        ]);

    $services->set(NoopAuthenticationManager::class);
    $services->set(AuthenticationManagerInterface::class, NoopAuthenticationManager::class);

    $services->set(RetryAuthenticationEntryPoint::class)
        ->args([abstract_arg('HTTP port'), abstract_arg('HTTPS port')]);

    $services->set(Firewall::class)
        ->args([service(FirewallMapInterface::class), service(EventDispatcherInterface::class)])
        ->tag('kernel.event_subscriber');

    $services->set(FirewallMap::class);
    $services->alias(FirewallMapInterface::class, FirewallMap::class);

    $services->set(LogoutUrlGenerator::class)
        ->args([
            service(RequestStack::class),
            service(UrlGeneratorInterface::class),
            service(TokenStorageInterface::class),
        ]);

    $services->set(SessionAuthenticationStrategy::class)
        ->args([abstract_arg('strategy')]);

    $services->alias(SessionAuthenticationStrategyInterface::class, SessionAuthenticationStrategy::class);

    // Listeners.
    $services->set(CheckCredentialsListener::class)
        ->args([service(PasswordHasherFactoryInterface::class)])
        ->tag('kernel.event_subscriber');

    $services->set(CookieClearingLogoutListener::class)
        ->abstract()
        ->args([abstract_arg('cookies')]);

    $services->set(CsrfProtectionListener::class)
        ->args([service(CsrfTokenManagerInterface::class)])
        ->tag('kernel.event_subscriber');

    $services->set(CsrfTokenClearingLogoutListener::class)
        ->args([service(ClearableTokenStorageInterface::class)])
        ->tag('kernel.event_subscriber');

    $services->set(DefaultLogoutListener::class)
        ->abstract()
        ->args([service(HttpUtils::class), abstract_arg('target URL')]);

    $services->set(PasswordMigratingListener::class)
        ->args([service(PasswordHasherFactoryInterface::class)])
        ->tag('kernel.event_subscriber');

    $services->set(SessionLogoutListener::class);

    $services->set(SessionStrategyListener::class)
        ->args([service(SessionAuthenticationStrategyInterface::class)]);

    $services->set(UserCheckerListener::class)
        ->abstract()
        ->args([abstract_arg('user checker')]);

    $services->set(UserProviderListener::class)
        ->abstract()
        ->args([abstract_arg('user provider')]);
    // End of - Listeners.

    // Firewalls.
    $services->set(AccessListener::class)
        ->args([
            service(TokenStorageInterface::class),
            service(AccessDecisionManagerInterface::class),
            service(AccessMapInterface::class),
            service(AuthenticationManagerInterface::class),
            false,
        ]);

    $services->set(AuthenticatorManagerListener::class)
        ->abstract()
        ->args([abstract_arg('authenticator manager')]);

    $services->set(ChannelListener::class)
        ->args([
            service(AccessMapInterface::class),
            service(RetryAuthenticationEntryPoint::class),
            service(LoggerInterface::class),
        ]);

    $services->set(ContextListener::class)
        ->abstract()
        ->args([
            service(TokenStorageInterface::class),
            abstract_arg('user providers'),
            abstract_arg('firewall'),
            service(LoggerInterface::class),
            service(EventDispatcherInterface::class),
            service(AuthenticationTrustResolverInterface::class),
        ]);

    $services->set(ExceptionListener::class)
        ->abstract()
        ->args([
            service(TokenStorageInterface::class),
            service(AuthenticationTrustResolverInterface::class),
            service(HttpUtils::class),
            abstract_arg('firewall'),
            abstract_arg('authentication entry point'),
            abstract_arg('error page'),
            abstract_arg('access denied handler'),
            service(LoggerInterface::class),
            abstract_arg('stateless'),
        ]);

    $services->set(LogoutListener::class)
        ->abstract()
        ->args([
            service(TokenStorageInterface::class),
            service(HttpUtils::class),
            abstract_arg('event dispatcher'),
            abstract_arg('options'),
            abstract_arg('CSRF token manager'),
        ]);
    // End of - Firewalls.
    // End of - Authentication.

    // Authorization.
    $services->set(AccessDecisionManager::class)
        ->args([
            tagged_iterator('security.voter'),
            abstract_arg('strategy'),
            abstract_arg('allow if all abstain decisions'),
            abstract_arg('allow if equal granted denied decisions'),
        ]);

    $services->alias(AccessDecisionManagerInterface::class, AccessDecisionManager::class);

    $services->set(AuthorizationChecker::class)
        ->args([
            service(TokenStorageInterface::class),
            service(AuthenticationManagerInterface::class),
            service(AccessDecisionManagerInterface::class),
            false,
            false,
        ]);

    $services->alias(AuthorizationCheckerInterface::class, AuthorizationChecker::class);

    $services->set(AccessMap::class);
    $services->alias(AccessMapInterface::class, AccessMap::class);

    // Voters.
    $services->set(AuthenticatedVoter::class)
        ->args([service(AuthenticationTrustResolverInterface::class)])
        ->tag('security.voter');

    $services->set(RoleVoter::class)
        ->tag('security.voter');
    // End of - Voters.
    // End of - Authorization.

    // CSRF.
    $services->set(UriSafeTokenGenerator::class);
    $services->set(TokenGeneratorInterface::class, UriSafeTokenGenerator::class);

    $services->set(SessionTokenStorage::class)
        ->args([service(SessionInterface::class)]);

    $services->alias(CsrfTokenStorageInterface::class, SessionTokenStorage::class);
    $services->alias(ClearableTokenStorageInterface::class, SessionTokenStorage::class);

    $services->set(CsrfTokenManager::class)
        ->args([
            service(TokenGeneratorInterface::class),
            service(CsrfTokenStorageInterface::class),
            service(RequestStack::class),
        ]);

    $services->alias(CsrfTokenManagerInterface::class, CsrfTokenManager::class);
    // End of - CSRF.

    // Other.
    $services->set(HttpUtils::class)
        ->args([service(UrlGeneratorInterface::class), service(UrlMatcherInterface::class)]);
    // End of - Other.
};
