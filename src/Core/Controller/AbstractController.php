<?php

namespace App\Core\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

abstract class AbstractController implements ServiceSubscriberInterface
{
    /**
     * @var ContainerInterface
     */
    protected ContainerInterface $container;

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedServices()
    {
        return [
            ContainerBagInterface::class,
            EngineInterface::class,
            EntityManagerInterface::class,
            UrlGeneratorInterface::class,
        ];
    }

    /**
     * @param ContainerInterface $container
     *
     * @required
     */
    public function setContainer(ContainerInterface $container): void
    {
        $this->container = $container;
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    protected function getParameter(string $name)
    {
        return $this->container
            ->get(ContainerBagInterface::class)
            ->get($name);
    }

    /**
     * @return EntityManagerInterface
     */
    protected function getEntityManager(): EntityManagerInterface
    {
        return $this->container->get(EntityManagerInterface::class);
    }

    /**
     * @param string $route
     * @param array  $parameters
     * @param int    $referenceType
     *
     * @return string
     */
    protected function generateUrl(
        string $route,
        array $parameters = [],
        int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH
    ): string {
        return $this->container
            ->get(UrlGeneratorInterface::class)
            ->generate($route, $parameters, $referenceType);
    }

    /**
     * @param string        $template
     * @param array         $parameters
     * @param Response|null $response
     *
     * @return Response
     */
    protected function render(string $template, array $parameters = [], Response $response = null): Response
    {
        $content = $this->container
            ->get(EngineInterface::class)
            ->render($template, $parameters);

        if (null === $response) {
            $response = new Response();
        }

        return $response->setContent($content);
    }
}
