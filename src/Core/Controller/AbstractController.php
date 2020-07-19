<?php

declare(strict_types=1);

namespace App\Core\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

abstract class AbstractController implements ServiceSubscriberInterface
{
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
            Serializer::class,
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

    /**
     * @param mixed $data
     * @param int   $status
     * @param array $headers
     * @param array $context
     *
     * @return JsonResponse
     */
    protected function json(
        $data,
        int $status = Response::HTTP_OK,
        array $headers = [],
        array $context = []
    ): JsonResponse {
        $json = $this->container
            ->get(Serializer::class)
            ->serialize(
                $data,
                'json',
                \array_merge(['json_encode_options' => JsonResponse::DEFAULT_ENCODING_OPTIONS], $context)
            );

        return new JsonResponse($json, $status, $headers, true);
    }

    /**
     * @param \SplFileInfo|string $file
     * @param string|null         $filename
     * @param string              $disposition
     *
     * @return BinaryFileResponse
     */
    protected function file(
        $file,
        string $filename = null,
        string $disposition = ResponseHeaderBag::DISPOSITION_ATTACHMENT
    ): BinaryFileResponse {
        $response = new BinaryFileResponse($file);
        $response->setContentDisposition(
            null !== $filename ? $filename : $response->getFile()->getFilename(),
            $disposition
        );

        return $response;
    }

    /**
     * @param string $url
     * @param int    $status
     *
     * @return RedirectResponse
     */
    protected function redirect(string $url, int $status = Response::HTTP_FOUND): RedirectResponse
    {
        return new RedirectResponse($url, $status);
    }

    /**
     * @param string $route
     * @param array  $parameters
     * @param int    $status
     *
     * @return RedirectResponse
     */
    protected function redirectToRoute(
        string $route,
        array $parameters = [],
        int $status = Response::HTTP_FOUND
    ): RedirectResponse {
        return $this->redirect($this->generateUrl($route, $parameters), $status);
    }
}
