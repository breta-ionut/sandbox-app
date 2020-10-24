<?php

declare(strict_types=1);

namespace App\User\Security;

use App\Api\Exception\ValidationException;
use App\Api\Http\RequestReader;
use App\Api\Http\ResponseFactory;
use App\User\Exception\AuthenticationFailedException;
use App\User\Model\Login;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolverInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
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
    private TokenStorageInterface $tokenStorage;
    private AuthenticationTrustResolverInterface $authenticationTrustResolver;
    private RequestReader $requestReader;
    private ValidatorInterface $validator;
    private UserProviderInterface $userProvider;
    private ResponseFactory $responseFactory;

    /**
     * @param string                               $loginPath
     * @param TokenStorageInterface                $tokenStorage
     * @param AuthenticationTrustResolverInterface $authenticationTrustResolver
     * @param RequestReader                        $requestReader
     * @param ValidatorInterface                   $validator
     * @param UserProviderInterface                $userProvider
     * @param ResponseFactory                      $responseFactory
     */
    public function __construct(
        string $loginPath,
        TokenStorageInterface $tokenStorage,
        AuthenticationTrustResolverInterface $authenticationTrustResolver,
        RequestReader $requestReader,
        ValidatorInterface $validator,
        UserProviderInterface $userProvider,
        ResponseFactory $responseFactory
    ) {
        $this->loginPath = $loginPath;
        $this->requestReader = $requestReader;
        $this->validator = $validator;
        $this->userProvider = $userProvider;
        $this->responseFactory = $responseFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function supports(Request $request): ?bool
    {
        return Request::METHOD_POST === $request->getMethod()
            && $request->getPathInfo() === $this->loginPath
            && !$this->authenticationTrustResolver->isFullFledged($this->tokenStorage->getToken());
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
        return $this->responseFactory->createFromData($token->getUser());
    }

    /**
     * {@inheritDoc}
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        throw new AuthenticationFailedException(0, $exception);
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
