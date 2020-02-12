<?php

namespace App\Core\Http;

use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use Symfony\Component\HttpFoundation\Session\Storage\SessionStorageInterface;
use Symfony\Component\HttpKernel\EventListener\AbstractSessionListener;

/**
 * Sets the session on the request and also ensures that the 'cookie_secure' option is set on the NativeSessionStorage
 * (if used) when the request is secure.
 */
class SessionListener extends AbstractSessionListener
{
    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    protected function getSession()
    {
        $request = $this->container
            ->get(RequestStack::class)
            ->getMasterRequest();

        if (null !== $request
            && $request->isSecure()
            && ($storage = $this->container->get(SessionStorageInterface::class)) instanceof NativeSessionStorage
        ) {
            $storage->setOptions(['cookie_secure' => true]);
        }

        return $this->container->get(SessionInterface::class);
    }
}
