<?php

declare(strict_types=1);

namespace App\Core\DependencyInjection;

use App\Core\Security\WatchdogAuthenticationListener;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Argument\IteratorArgument;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpFoundation\RequestMatcher;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;
use Symfony\Component\Security\Http\Firewall\AccessListener;
use Symfony\Component\Security\Http\Firewall\AnonymousAuthenticationListener;
use Symfony\Component\Security\Http\Firewall\ChannelListener;
use Symfony\Component\Security\Http\Firewall\ContextListener;
use Symfony\Component\Security\Http\Firewall\ExceptionListener;

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
     *
     * @return Reference
     */
    private function createWatchdogListener(
        ContainerBuilder $container,
        string $firewall,
        array $firewallConfig
    ): Reference {
        $id = 'security.watchdog_listener.'.$firewall;

        $definition = (new ChildDefinition(WatchdogAuthenticationListener::class))
            ->setArgument('$firewall', $firewall)
            ->setArgument('$authenticator', new Reference($firewallConfig['watchdog']));

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

    private function createLogoutListener(ContainerBuilder $container, string $firewall, array $logoutConfig): Reference
    {
        $id = 'security.logout_listener.'.$firewall;

        $options = \array_filter([
            'logout_path' => $logoutConfig['path'] ?? null,
            'csrf_parameter' => $logoutConfig['csrf_parameter'] ?? null,
            'csrf_token_id' => $logoutConfig['csrf_token_id'] ?? null,
        ], fn($value) => null !== $value);
    }

    private function createFirewall(ContainerBuilder $container, string $name, array $firewallConfig): array
    {
        $requestMatcher = $this->createRequestMatcher($container, $firewallConfig);

        // Create listeners.
        $listeners = [new Reference(ChannelListener::class)];

        $stateless = $firewallConfig['stateless'];
        if (!$stateless) {
            $listeners[] = $this->createContextListener($container, $name, $firewallConfig);
        }

        if (isset($firewallConfig['watchdog'])) {
            $watchdogListener = $this->createWatchdogListener($container, $name, $firewallConfig);

            $listeners[] = $watchdogListener;
            $entryPoint = $watchdogListener;
        }

        if ($firewallConfig['anonymous']) {
            $listeners[] = new Reference(AnonymousAuthenticationListener::class);
        }

        $listeners[] = new Reference(AccessListener::class);

        $exceptionListener = $this->createExceptionListener($container, $name, $firewallConfig, $entryPoint ?? null);
    }

    private function createFirewalls(ContainerBuilder $container, array $firewallsConfig): void
    {

    }
}
