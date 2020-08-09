<?php

declare(strict_types=1);

namespace App\User\Security;

use App\Api\Exception\MalformedInputException;
use App\Api\Exception\ValidationException;
use App\User\Exception\LoginException;
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
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class Authenticator extends AbstractAuthenticator
{
    private string $loginPath;
    private SerializerInterface $serializer;
    private ValidatorInterface $validator;
    private UserProviderInterface $userProvider;

    /**
     * @param string                $loginPath
     * @param SerializerInterface   $serializer
     * @param ValidatorInterface    $validator
     * @param UserProviderInterface $userProvider
     */
    public function __construct(
        string $loginPath,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        UserProviderInterface $userProvider
    ) {
        $this->loginPath = $loginPath;
        $this->serializer = $serializer;
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
        $credentials = $this->getCredentials($request);
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
        throw new LoginException(0, $exception);
    }

    /**
     * @param Request $request
     *
     * @return Login
     *
     * @throws MalformedInputException
     */
    private function getCredentials(Request $request): Login
    {
        try {
            return $this->serializer->deserialize($request->getContent(), Login::class, 'json');
        } catch (ExceptionInterface $exception) {
            throw new MalformedInputException(0, $exception);
        }
    }

    /**
     * @param Login $login
     *
     * @throws ValidationException
     */
    private function validateCredentials(Login $login): void
    {
        $violations = $this->validator->validate($login);
        if (0 !== \count($violations)) {
            throw new ValidationException($violations);
        }
    }
}
