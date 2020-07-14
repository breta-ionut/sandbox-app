<?php

declare(strict_types=1);

namespace App\User\Security;

use App\Api\Errors;
use App\Api\Http\ResponseTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\PassportInterface;
use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

class Authenticator extends AbstractAuthenticator
{
    use ResponseTrait;

    private string $loginPath;
    private DecoderInterface $decoder;
    private UserProviderInterface $userProvider;

    /**
     * @param string                $loginPath
     * @param DecoderInterface      $decoder
     * @param UserProviderInterface $userProvider
     */
    public function __construct(string $loginPath, DecoderInterface $decoder, UserProviderInterface $userProvider)
    {
        $this->loginPath = $loginPath;
        $this->decoder = $decoder;
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

        $user = $this->userProvider->loadUserByUsername($credentials['username']);

        return new Passport($user, new PasswordCredentials($credentials['password']));
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
        return $this->createErrorResponse(Response::HTTP_FORBIDDEN, Errors::LOGIN_FAILURE, 'Login failed.');
    }

    /**
     * @param Request $request
     *
     * @return array
     *
     * @throws BadRequestHttpException
     */
    private function getCredentials(Request $request): array
    {
        $data = $this->getDataFromRequest($request);
        $credentials = [];

        foreach (['username', 'password'] as $key) {
            if (!isset($data[$key])) {
                throw new BadRequestHttpException(\sprintf('The "%s" key is missing.', $key));
            }

            if (!\is_string($data[$key])) {
                throw new BadRequestHttpException(\sprintf('The "%s" key should be a string.', $key));
            }

            $credentials[$key] = $data[$key];
        }

        if (\strlen($credentials['username']) > Security::MAX_USERNAME_LENGTH) {
            throw new BadRequestHttpException('Invalid username.');
        }

        return $credentials;
    }

    /**
     * @param Request $request
     *
     * @return array
     *
     * @throws BadRequestHttpException
     */
    private function getDataFromRequest(Request $request): array
    {
        try {
            $data = $this->decoder->decode($request->getContent(), 'json');

            if (!\is_array($data)) {
                throw new BadRequestHttpException('Invalid JSON.');
            }
        } catch (ExceptionInterface $exception) {
            throw new BadRequestHttpException('Invalid JSON.', $exception);
        }

        return $data;
    }
}
