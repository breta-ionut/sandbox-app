<?php

declare(strict_types=1);

namespace App\Core\DependencyInjection;

use App\Core\EventDispatcher\BubblingEventDispatcher;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Argument\IteratorArgument;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpFoundation\RequestMatcher;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactory;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManager;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Http\AccessMap;
use Symfony\Component\Security\Http\Authentication\AuthenticatorManager;
use Symfony\Component\Security\Http\EntryPoint\RetryAuthenticationEntryPoint;
use Symfony\Component\Security\Http\EventListener\CookieClearingLogoutListener;
use Symfony\Component\Security\Http\EventListener\DefaultLogoutListener;
use Symfony\Component\Security\Http\EventListener\SessionLogoutListener;
use Symfony\Component\Security\Http\EventListener\SessionStrategyListener;
use Symfony\Component\Security\Http\EventListener\UserCheckerListener;
use Symfony\Component\Security\Http\EventListener\UserProviderListener;
use Symfony\Component\Security\Http\Firewall\AccessListener;
use Symfony\Component\Security\Http\Firewall\AuthenticatorManagerListener;
use Symfony\Component\Security\Http\Firewall\ChannelListener;
use Symfony\Component\Security\Http\Firewall\ContextListener;
use Symfony\Component\Security\Http\Firewall\ExceptionListener;
use Symfony\Component\Security\Http\Firewall\LogoutListener;
use Symfony\Component\Security\Http\FirewallMap;
use Symfony\Component\Security\Http\Logout\LogoutUrlGenerator;
use Symfony\Component\Security\Http\Session\SessionAuthenticationStrategy;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class SecurityExtension extends ConfigurableExtension
{
    use ConfigurationExtensionTrait;

    /**
     * {@inheritDoc}
     */
    protected function loadInternal(array $mergedConfig, ContainerBuilder $container): void
    {
        $loader = new PhpFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('security.php');

        $this->createPasswordHashers($container, $mergedConfig['password_hashers']);
        $this->createFirewalls($container, $mergedConfig['firewalls']);
        $this->configureAccessMap($container, $mergedConfig['access_control']);
        $this->configureOther($container, $mergedConfig);

        $container->registerForAutoconfiguration(VoterInterface::class)->addTag('security.voter');
    }

    private function createPasswordHashers(ContainerBuilder $container, array $passwordHashersConfig): void
    {
        foreach ($passwordHashersConfig as $class => $passwordHasherConfig) {
            if (isset($passwordHasherConfig['id'])) {
                $passwordHashersConfig[$class] = new Reference($passwordHasherConfig['id']);
            }
        }

        $container->getDefinition(PasswordHasherFactory::class)
            ->setArgument('$passwordHashers', $passwordHashersConfig);
    }

    private function createFirewalls(ContainerBuilder $container, array $firewallsConfig): void
    {
        $firewallMapDefinition = $container->getDefinition(FirewallMap::class);

        foreach ($firewallsConfig as $name => $firewallConfig) {
            [
                $requestMatcher,
                $listeners,
                $exceptionListener,
                $logoutListener
            ] = $this->createFirewall($container, $name, $firewallConfig);

            $firewallMapDefinition->addMethodCall(
                'add',
                [$requestMatcher, $listeners, $exceptionListener, $logoutListener]
            );
        }
    }

    private function createFirewall(ContainerBuilder $container, string $name, array $firewallConfig): array
    {
        $requestMatcher = $this->createRequestMatcher($container, $firewallConfig);

        if (!$firewallConfig['security']) {
            return [$requestMatcher, [], null, null, [], null];
        }

        $eventDispatcherId = $this->createEventDispatcher($container, $name);

        $listeners = [new Reference(ChannelListener::class)];

        if (!$firewallConfig['stateless']) {
            $listeners[] = $this->createContextListener($container, $name, $firewallConfig);

            $container->getDefinition(SessionStrategyListener::class)
                ->addTag('kernel.event_subscriber', ['dispatcher' => $eventDispatcherId]);
        }

        if ($firewallConfig['authenticators']) {
            $listeners[] = $this->createAuthenticatorManagerListener(
                $container,
                $name,
                $firewallConfig,
                $eventDispatcherId
            );
        }

        $listeners[] = new Reference(AccessListener::class);

        $exceptionListener = $this->createExceptionListener($container, $name, $firewallConfig);

        if ($firewallConfig['logout']['enabled']) {
            $logoutListener = $this->createLogoutListener($container, $name, $firewallConfig, $eventDispatcherId);
        }

        return [$requestMatcher, $listeners, $exceptionListener, $logoutListener ?? null];
    }

    private function createRequestMatcher(ContainerBuilder $container, array $requestMatcherConfig): Reference
    {
        $arguments = [
            $requestMatcherConfig['path'] ?? null,
            $requestMatcherConfig['host'] ?? null,
            $requestMatcherConfig['methods'],
            $requestMatcherConfig['ips'],
            $requestMatcherConfig['attributes'],
            $requestMatcherConfig['scheme'] ?? null,
            $requestMatcherConfig['port'] ?? null
        ];
        $id = 'security.request_matcher.'.ContainerBuilder::hash($arguments);

        $container->setDefinition($id, new Definition(RequestMatcher::class, $arguments));

        return new Reference($id);
    }

    private function createEventDispatcher(ContainerBuilder $container, string $firewall): string
    {
        $eventDispatcherId = 'security.event_dispatcher.'.$firewall;
        $container->register($eventDispatcherId, BubblingEventDispatcher::class)
            ->setArguments([new Reference(EventDispatcherInterface::class)]);

        return $eventDispatcherId;
    }

    private function createContextListener(
        ContainerBuilder $container,
        string $firewall,
        array $firewallConfig
    ): Reference {
        $id = 'security.context_listener.'.$firewall;

        $userProviders = [];
        if (isset($firewallConfig['user_provider'])) {
            $userProviders[] = new Reference($firewallConfig['user_provider']);
        }

        $definition = (new ChildDefinition(ContextListener::class))
            ->setArgument('$userProviders', new IteratorArgument($userProviders))
            ->setArgument('$contextKey', $firewall);

        $container->setDefinition($id, $definition);

        return new Reference($id);
    }

    private function createAuthenticatorManagerListener(
        ContainerBuilder $container,
        string $firewall,
        array $firewallConfig,
        string $eventDispatcherId
    ): Reference {
        $authenticatorManagerId = 'security.authenticator_manager.'.$firewall;

        $authenticators = \array_map(
            fn(string $authenticatorId): Reference => new Reference($authenticatorId),
            $firewallConfig['authenticators']
        );
        $authenticatorManagerDefinition = (new ChildDefinition(AuthenticatorManager::class))
            ->setArgument('$authenticators', new IteratorArgument($authenticators))
            ->setArgument('$eventDispatcher', new Reference($eventDispatcherId))
            ->setArgument('$firewallName', $firewall);

        $container->setDefinition($authenticatorManagerId, $authenticatorManagerDefinition);

        $id = 'security.authenticator_manager_listener'.$firewall;

        $definition = (new ChildDefinition(AuthenticatorManagerListener::class))
            ->setArgument('$authenticationManager', new Reference($authenticatorManagerId));

        $container->setDefinition($id, $definition);

        // Configure AuthenticatorManager listeners.
        if (isset($firewallConfig['user_provider'])) {
            $userProvider = new Reference($firewallConfig['user_provider']);
            $userProviderListenerDefinition = (new ChildDefinition(UserProviderListener::class))
                ->setArgument('$userProvider', $userProvider)
                ->addTag('kernel.event_listener', [
                    'method' => 'checkPassport',
                    'priority' => 1024,
                    'dispatcher' => $eventDispatcherId,
                ]);

            $container->setDefinition('security.user_provider_listener.'.$firewall, $userProviderListenerDefinition);
        }

        if (isset($firewallConfig['user_checker'])) {
            $userCheckerListenerDefinition = (new ChildDefinition(UserCheckerListener::class))
                ->setArgument('$userChecker', new Reference($firewallConfig['user_checker']))
                ->addTag('kernel.event_subscriber', ['dispatcher' => $eventDispatcherId]);

            $container->setDefinition('security.user_checker_listener.' . $firewall, $userCheckerListenerDefinition);
        }

        return new Reference($id);
    }

    private function createExceptionListener(
        ContainerBuilder $container,
        string $firewall,
        array $firewallConfig
    ): Reference {
        $id = 'security.exception_listener.'.$firewall;

        $entryPoint = isset($firewallConfig['entry_point']) ? new Reference($firewallConfig['entry_point']) : null;
        $definition = (new ChildDefinition(ExceptionListener::class))
            ->setArgument('$firewallName', $firewall)
            ->setArgument('$authenticationEntryPoint', $entryPoint)
            ->setArgument('$errorPage', $firewallConfig['access_denied_url'] ?? null)
            ->setArgument(
                '$accessDeniedHandler',
                isset($firewallConfig['access_denied_handler'])
                    ? new Reference($firewallConfig['access_denied_handler'])
                    : null
            )
            ->setArgument('$stateless', $firewallConfig['stateless']);

        $container->setDefinition($id, $definition);

        return new Reference($id);
    }

    private function createLogoutListener(
        ContainerBuilder $container,
        string $firewall,
        array $firewallConfig,
        string $eventDispatcherId
    ): Reference {
        $id = 'security.logout_listener.'.$firewall;

        $logoutConfig = $firewallConfig['logout'];

        $options = [
            'logout_path' => $logoutConfig['path'],
            'csrf_parameter' => $logoutConfig['csrf']['parameter'],
            'csrf_token_id' => $logoutConfig['csrf']['token_id'],
        ];
        $csrfTokenManager = $logoutConfig['csrf']['enabled'] ? new Reference(CsrfTokenManagerInterface::class) : null;

        $definition = (new ChildDefinition(LogoutListener::class))
            ->setArgument('$eventDispatcher', new Reference($eventDispatcherId))
            ->setArgument('$options', $options)
            ->setArgument('$csrfTokenManager', $csrfTokenManager);

        $container->setDefinition($id, $definition);

        // Configure logout listeners.
        if (isset($logoutConfig['target'])) {
            $defaultListenerDefinition = (new ChildDefinition(DefaultLogoutListener::class))
                ->setArgument('$targetUrl', $logoutConfig['target'])
                ->addTag('kernel.event_subscriber', ['dispatcher' => $eventDispatcherId]);

            $container->setDefinition('security.logout_default_listener.' . $firewall, $defaultListenerDefinition);
        }

        if (!$firewallConfig['stateless'] && $logoutConfig['invalidate_session']) {
            $container->getDefinition(SessionLogoutListener::class)
                ->addTag('kernel.event_subscriber', ['dispatcher' => $eventDispatcherId]);
        }

        if ($logoutConfig['clear_cookies']) {
            $cookiesClearingListenerDefinition = (new ChildDefinition(CookieClearingLogoutListener::class))
                ->setArgument('$cookies', $logoutConfig['clear_cookies'])
                ->addTag('kernel.event_subscriber', ['dispatcher' => $eventDispatcherId]);

            $container->setDefinition(
                'security.logout_cookie_clearing_listener.'.$firewall,
                $cookiesClearingListenerDefinition
            );
        }

        // Register listener to LogoutUrlGenerator.
        $container->getDefinition(LogoutUrlGenerator::class)
            ->addMethodCall('registerListener', [
                $firewall,
                $options['logout_path'],
                $options['csrf_token_id'],
                $options['csrf_parameter'],
                $csrfTokenManager
            ]);

        return new Reference($id);
    }

    private function configureAccessMap(ContainerBuilder $container, array $accessControlConfig): void
    {
        $accessMapDefinition = $container->getDefinition(AccessMap::class);

        foreach ($accessControlConfig as $accessControlEntryConfig) {
            $requestMatcher = $this->createRequestMatcher($container, $accessControlEntryConfig);

            $accessMapDefinition->addMethodCall(
                'add',
                [$requestMatcher, $accessControlEntryConfig['roles'], $accessControlEntryConfig['channel'] ?? null]
            );
        }
    }

    private function configureOther(ContainerBuilder $container, array $config): void
    {
        $container->getDefinition(AuthenticatorManager::class)
            ->setArgument('$eraseCredentials', $config['erase_credentials']);

        $container->getDefinition(SessionAuthenticationStrategy::class)
            ->setArgument('$strategy', $config['session_authentication_strategy']);

        $container->getDefinition(AccessDecisionManager::class)
            ->setArgument('$strategy', $config['access_decision_manager']['strategy'])
            ->setArgument('$allowIfAllAbstainDecisions', $config['access_decision_manager']['allow_if_all_abstain'])
            ->setArgument(
                '$allowIfEqualGrantedDeniedDecisions',
                $config['access_decision_manager']['allow_if_equal_granted_denied']
            );

        $container->getDefinition(RetryAuthenticationEntryPoint::class)
            ->setArgument('$httpPort', $config['http_port'])
            ->setArgument('$httpsPort', $config['https_port']);
    }
}
