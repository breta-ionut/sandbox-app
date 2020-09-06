<?php

declare(strict_types=1);

namespace App\User\Security;

use App\Api\Exception\ValidationException;
use App\Api\Http\RequestReader;
use App\User\Model\Login;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\PassportInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class Authenticator extends AbstractAuthenticator
{
    private string $loginPath;
    private RequestReader $requestReader;
    private ValidatorInterface $validator;
    private UserProviderInterface $userProvider;

    /**
     * @param string                $loginPath
     * @param RequestReader         $requestReader
     * @param ValidatorInterface    $validator
     * @param UserProviderInterface $userProvider
     */
    public function __construct(
        string $loginPath,
        RequestReader $requestReader,
        ValidatorInterface $validator,
        UserProviderInterface $userProvider
    ) {
        $this->loginPath = $loginPath;
        $this->requestReader = $requestReader;
        $this->validator = $validator;
        $this->userProvider = $userProvider;
    }

    /**
     * {@inheritDoc}
     */
    public function supports(Request $request): ?bool
    {
        return Request::METHOD_POST === $request->getMethod() && $request->getPathInfo() === $this->loginPath;
    }

    /**
     * {@inheritDoc}
     */
    public function authenticate(Request $request): PassportInterface
    {
        /** @var Login $credentials */
        $credentials = $this->requestReader->read($request, Login::class);
        $this->validateCredentials($credentials);

        $user = $this->userProvider->loadUserByUsername($credentials->getUsername());

        return new Passport($user, new PasswordCredentials($credentials->getPassword()));
    }

    /**
     * {@inheritDoc}
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        throw $exception;
    }

    /**
     * @param Login $credentials
     *
     * @throws ValidationException
     */
    private function validateCredentials(Login $credentials): void
    {
        $violations = $this->validator->validate($credentials);
        if (\count($violations)) {
            throw new ValidationException($violations);
        }
    }
}
