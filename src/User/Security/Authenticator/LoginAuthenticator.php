<?php

declare(strict_types=1);

namespace App\User\Security\Authenticator;

use App\Api\Exception\ValidationException;
use App\Api\Http\RequestReader;
use App\User\Exception\AuthenticationFailedException;
use App\User\Model\Login;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\PassportInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class LoginAuthenticator extends AbstractAuthenticator
{
    private RequestReader $requestReader;
    private ValidatorInterface $validator;

    /**
     * @param RequestReader      $requestReader
     * @param ValidatorInterface $validator
     */
    public function __construct(RequestReader $requestReader, ValidatorInterface $validator)
    {
        $this->requestReader = $requestReader;
        $this->validator = $validator;
    }

    /**
     * {@inheritDoc}
     */
    public function supports(Request $request): ?bool
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function authenticate(Request $request): PassportInterface
    {
        /** @var Login $credentials */
        $credentials = $this->requestReader->read($request, Login::class);
        $this->validateCredentials($credentials);

        return new Passport(
            new UserBadge($credentials->getUsername()),
            new PasswordCredentials($credentials->getPassword())
        );
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
