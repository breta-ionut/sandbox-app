<?php

declare(strict_types=1);

namespace App\Core\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

/**
 * The contract of watchdog authenticators. Through them one can implement various authentication systems, adapted to
 * its own needs (e.g. form login, API token based authentication).
 *
 * The watchdog authenticator concentrates all methods used throughout the authentication flow in one place, providing a
 * better overview and a higher degree of control over the process.
 */
interface WatchdogAuthenticatorInterface extends AuthenticationEntryPointInterface
{
    /**
     * Determines if the authenticator supports the given request.
     *
     * @param Request $request
     *
     * @return bool
     */
    public function supports(Request $request): bool;

    /**
     * Extracts the authentication credentials from the given request.
     *
     * @param Request $request
     *
     * @return mixed A non-null value.
     */
    public function getCredentials(Request $request);

    /**
     * Determines the user associated to the previously extracted credentials. If null is returned (no user found), the
     * authentication fails (an UsernameNotFoundException gets thrown further). The authentication can also be stopped
     * by throwing an AuthenticationException directly from the method.
     *
     * @param mixed $credentials
     * @param UserProviderInterface $userProvider
     *
     * @return UserInterface|null
     *
     * @throws AuthenticationException
     */
    public function getUser($credentials, UserProviderInterface $userProvider): ?UserInterface;

    /**
     * Checks if the previously determined credentials are valid. If they aren't (false is returned), the authentication
     * fails. The authentication can also be stopped by throwing an AuthenticationException.
     *
     * @param $credentials
     * @param UserInterface $user
     *
     * @return bool
     *
     * @throws AuthenticationException
     */
    public function checkCredentials($credentials, UserInterface $user): bool;

    /**
     * Creates an authenticated token for the given user and firewall.
     *
     * @param UserInterface $user
     * @param string $firewall
     *
     * @return WatchdogTokenInterface
     */
    public function createAuthenticatedToken(UserInterface $user, string $firewall): WatchdogTokenInterface;

    /**
     * Called when the authentication fails. The resulting response, if any, gets returned immediately, otherwise the
     * request continues. Exceptions can also be thrown from this method in the idea that they will get handled later
     * (i.e. by an exception listener).
     *
     * @param Request $request
     * @param AuthenticationException $exception
     *
     * @return Response|null
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response;

    /**
     * Called when the authentication succeeds. The resulting response, if any, gets returned immediately, otherwise the
     * request continues.
     *
     * @param Request $request
     * @param WatchdogTokenInterface $token
     *
     * @return Response|null
     */
    public function onAuthenticationSuccess(Request $request, WatchdogTokenInterface $token): ?Response;
}
