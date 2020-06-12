<?php

declare(strict_types=1);

namespace App\Core\DependencyInjection;

use App\Core\EventDispatcher\BubblingEventDispatcher;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Argument\IteratorArgument;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpFoundation\RequestMatcher;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;
use Symfony\Component\Security\Core\Authentication\AuthenticationProviderManager;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManager;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;
use Symfony\Component\Security\Http\AccessMap;
use Symfony\Component\Security\Http\EntryPoint\RetryAuthenticationEntryPoint;
use Symfony\Component\Security\Http\EventListener\CookieClearingLogoutListener;
use Symfony\Component\Security\Http\EventListener\DefaultLogoutListener;
use Symfony\Component\Security\Http\EventListener\SessionLogoutListener;
use Symfony\Component\Security\Http\EventListener\SessionStrategyListener;
use Symfony\Component\Security\Http\Firewall\AccessListener;
use Symfony\Component\Security\Http\Firewall\ChannelListener;
use Symfony\Component\Security\Http\Firewall\ContextListener;
use Symfony\Component\Security\Http\Firewall\ExceptionListener;
use Symfony\Component\Security\Http\Firewall\LogoutListener;
use Symfony\Component\Security\Http\FirewallMap;
use Symfony\Component\Security\Http\Session\SessionAuthenticationStrategy;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class SecurityExtension extends ConfigurableExtension
{
    use ConfigurationExtensionTrait;

    /**
     * {@inheritDoc}
     */
    protected function loadInternal(array $mergedConfig, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('security.yaml');

        $this->createEncoders($container, $mergedConfig['encoders']);
        $this->createFirewalls($container, $mergedConfig['firewalls']);
        $this->configureAccessMap($container, $mergedConfig['access_control']);
        $this->configureOther($container, $mergedConfig);

        $container->registerForAutoconfiguration(VoterInterface::class)->addTag('security.voter');
    }

    /**
     * @param ContainerBuilder $container
     * @param array            $encodersConfig
     */
    private function createEncoders(ContainerBuilder $container, array $encodersConfig): void
    {
        foreach ($encodersConfig as $class => $encoderConfig) {
            if (isset($encoderConfig['id'])) {
                $encodersConfig[$class] = new Reference($encoderConfig['id']);
            }
        }

        $container->getDefinition(EncoderFactory::class)->setArgument('$encoders', $encodersConfig);
    }

    /**
     * @param ContainerBuilder $container
     * @param array            $requestMatcherConfig
     *
     * @return Reference
     */
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

    /**
     * @param ContainerBuilder $container
     * @param string           $firewall
     *
     * @return string
     */
    private function createEventDispatcher(ContainerBuilder $container, string $firewall): string
    {
        $eventDispatcherId = 'security.event_dispatcher.'.$firewall;
        $container->register($eventDispatcherId, BubblingEventDispatcher::class)
            ->setArguments([new Reference(EventDispatcherInterface::class)]);

        return $eventDispatcherId;
    }

    /**
     * @param ContainerBuilder $container
     * @param string           $firewall
     * @param array            $firewallConfig
     *
     * @return Reference
     */
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

    /**
     * @param ContainerBuilder $container
     * @param string           $firewall
     * @param array            $firewallConfig
     * @param Reference|null   $entryPoint
     *
     * @return Reference
     */
    private function createExceptionListener(
        ContainerBuilder $container,
        string $firewall,
        array $firewallConfig,
        ?Reference $entryPoint
    ): Reference {
        $id = 'security.exception_listener.'.$firewall;

        $definition = (new ChildDefinition(ExceptionListener::class))
            ->setArgument('$providerKey', $firewall)
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

    /**
     * @param ContainerBuilder $container
     * @param string           $firewall
     * @param array            $logoutConfig
     * @param string           $eventDispatcherId
     *
     * @return Reference
     */
    private function createLogoutListener(
        ContainerBuilder $container,
        string $firewall,
        array $logoutConfig,
        string $eventDispatcherId
    ): Reference {
        $id = 'security.logout_listener.'.$firewall;

        $options = \array_filter([
            'logout_path' => $logoutConfig['path'] ?? null,
            'csrf_parameter' => $logoutConfig['csrf_parameter'] ?? null,
            'csrf_token_id' => $logoutConfig['csrf_token_id'] ?? null,
        ], fn($value) => null !== $value);

        $definition = (new ChildDefinition(LogoutListener::class))
            ->setArgument('$eventDispatcher', new Reference($eventDispatcherId))
            ->setArgument('$options', $options);

        $container->setDefinition($id, $definition);

        // Configure logout listeners.
        $defaultListenerDefinition = (new ChildDefinition(DefaultLogoutListener::class))
            ->setArgument('$targetUrl', $logoutConfig['target'])
            ->addTag('kernel.event_subscriber', ['dispatcher' => $eventDispatcherId]);

        $container->setDefinition('security.logout_default_listener.'.$firewall, $defaultListenerDefinition);

        if ($logoutConfig['invalidate_session']) {
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

        return new Reference($id);
    }

    /**
     * @param ContainerBuilder $container
     * @param string           $name
     * @param array            $firewallConfig
     *
     * @return array
     */
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

        $listeners[] = new Reference(AccessListener::class);

        $entryPoint = isset($firewallConfig['entry_point']) ? new Reference($firewallConfig['entry_point']) : null;
        $exceptionListener = $this->createExceptionListener($container, $name, $firewallConfig, $entryPoint);

        if ($firewallConfig['logout']['enabled']) {
            $logoutListener = $this->createLogoutListener(
                $container,
                $name,
                $firewallConfig['logout'],
                $eventDispatcherId
            );
        }

        return [$requestMatcher, $listeners, $exceptionListener, $logoutListener ?? null];
    }

    /**
     * @param ContainerBuilder $container
     * @param array            $firewallsConfig
     */
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

    /**
     * @param ContainerBuilder $container
     * @param array            $accessControlConfig
     */
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

    /**
     * @param ContainerBuilder $container
     * @param array            $config
     */
    private function configureOther(ContainerBuilder $container, array $config): void
    {
        $container->getDefinition(AuthenticationProviderManager::class)
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
