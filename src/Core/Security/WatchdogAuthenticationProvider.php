<?php

declare(strict_types=1);

namespace App\Core\Security;

use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationExpiredException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class WatchdogAuthenticationProvider implements AuthenticationProviderInterface
{
    private string $firewall;
    private WatchdogAuthenticatorInterface $authenticator;
    private UserProviderInterface $userProvider;
    private UserCheckerInterface $userChecker;

    /**
     * @param string                         $firewall
     * @param WatchdogAuthenticatorInterface $authenticator
     * @param UserProviderInterface          $userProvider
     * @param UserCheckerInterface           $userChecker
     */
    public function __construct(
        string $firewall,
        WatchdogAuthenticatorInterface $authenticator,
        UserProviderInterface $userProvider,
        UserCheckerInterface $userChecker
    ) {
        $this->firewall = $firewall;
        $this->authenticator = $authenticator;
        $this->userProvider = $userProvider;
        $this->userChecker = $userChecker;
    }

    /**
     * {@inheritDoc}
     */
    public function authenticate(TokenInterface $token)
    {
        if (!$token instanceof PreAuthenticationWatchdogToken) {
            // A re-authentication of a previously authenticated token is demanded.

            // Dealing with a still-authenticated token here is very unlikely to happen and can be at most caused by a
            // logic error. But technically, since the role of the authentication provider is to authenticate tokens and
            // this one is already authenticated, it is safe then to return as it is.
            if ($token->isAuthenticated()) {
                return $token;
            }

            // Tokens can get de-authenticated over time (by a user change, for example). Since a re-authentication
            // cannot be done without having the credentials, the only possible choice here is to force a logout (which
            // will trigger a normal authentication).
            throw new AuthenticationExpiredException();
        }

        // Retrieve user.
        $user = $this->authenticator->getUser($token->getCredentials(), $this->userProvider);
        if (null === $user) {
            throw new UsernameNotFoundException(\sprintf(
                'User to be authenticated could not be determined by watchdog authenticator on firewall "%s".',
                $this->firewall
            ));
        }

        // Check credentials.
        $this->userChecker->checkPreAuth($user);

        if (!$this->authenticator->checkCredentials($token->getCredentials(), $user)) {
            throw new BadCredentialsException(\sprintf(
                'Invalid credentials determined by watchdog authenticator on firewall "%s".',
                $this->firewall
            ));
        }

        $this->userChecker->checkPostAuth($user);

        // Create and return the authenticated token with the authenticator.
        return $this->authenticator->createAuthenticatedToken($user, $this->firewall);
    }

    /**
     * {@inheritDoc}
     */
    public function supports(TokenInterface $token)
    {
        return $token instanceof WatchdogTokenInterface && $token->getFirewall() === $this->firewall;
    }
}
