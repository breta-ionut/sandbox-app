<?php

declare(strict_types=1);

namespace App\Core\DependencyInjection;

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
use Symfony\Component\Security\Http\Firewall\AccessListener;
use Symfony\Component\Security\Http\Firewall\ChannelListener;
use Symfony\Component\Security\Http\Firewall\ContextListener;
use Symfony\Component\Security\Http\Firewall\ExceptionListener;
use Symfony\Component\Security\Http\Firewall\LogoutListener;
use Symfony\Component\Security\Http\FirewallMap;
use Symfony\Component\Security\Http\Logout\CookieClearingLogoutHandler;
use Symfony\Component\Security\Http\Logout\CsrfTokenClearingLogoutHandler;
use Symfony\Component\Security\Http\Logout\DefaultLogoutSuccessHandler;
use Symfony\Component\Security\Http\Logout\SessionLogoutHandler;
use Symfony\Component\Security\Http\Session\SessionAuthenticationStrategy;

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
     *
     * @return Reference
     */
    private function createLogoutListener(ContainerBuilder $container, string $firewall, array $logoutConfig): Reference
    {
        $id = 'security.logout_listener.'.$firewall;

        $options = \array_filter([
            'logout_path' => $logoutConfig['path'] ?? null,
            'csrf_parameter' => $logoutConfig['csrf_parameter'] ?? null,
            'csrf_token_id' => $logoutConfig['csrf_token_id'] ?? null,
        ], fn($value) => null !== $value);

        // Determine success handler.
        if (isset($logoutConfig['success_handler'])) {
            $successHandler = new Reference($logoutConfig['success_handler']);
        } else {
            $successHandlerId = 'security.logout_success_handler.'.$firewall;
            $successHandlerDefinition = (new ChildDefinition(DefaultLogoutSuccessHandler::class))
                ->setArgument('$targetUrl', $logoutConfig['target']);

            $container->setDefinition($successHandlerId, $successHandlerDefinition);

            $successHandler = new Reference($successHandlerId);
        }

        // Determine handlers.
        $handlers = [new Reference(CsrfTokenClearingLogoutHandler::class)];

        if ($logoutConfig['invalidate_session']) {
            $handlers[] = new Reference(SessionLogoutHandler::class);
        }

        if ($logoutConfig['clear_cookies']) {
            $clearCookiesHandlerId = 'security.logout_clear_cookies_handler.'.$firewall;
            $clearCookiesHandlerDefinition = (new ChildDefinition(CookieClearingLogoutHandler::class))
                ->setArgument('$cookies', $logoutConfig['clear_cookies']);

            $container->setDefinition($clearCookiesHandlerId, $clearCookiesHandlerDefinition);

            $handlers[] = new Reference($clearCookiesHandlerId);
        }

        foreach ($logoutConfig['handlers'] as $handlerId) {
            $handlers[] = new Reference($handlerId);
        }

        $definition = (new ChildDefinition(LogoutListener::class))
            ->setArgument('$options', $options)
            ->setArgument('$successHandler', $successHandler);

        foreach ($handlers as $handler) {
            $definition->addMethodCall('addHandler', [$handler]);
        }

        $container->setDefinition($id, $definition);

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

        $listeners = [new Reference(ChannelListener::class)];

        if (!$firewallConfig['stateless']) {
            $listeners[] = $this->createContextListener($container, $name, $firewallConfig);
        }

        $listeners[] = new Reference(AccessListener::class);

        $exceptionListener = $this->createExceptionListener($container, $name, $firewallConfig, $entryPoint ?? null);

        if ($firewallConfig['logout']['enabled']) {
            $logoutListener = $this->createLogoutListener($container, $name, $firewallConfig['logout']);
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
