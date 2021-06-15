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
    public static function getSubscribedServices(): array
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
     * @required
     */
    public function setContainer(ContainerInterface $container): void
    {
        $this->container = $container;
    }

    protected function getParameter(string $name): mixed
    {
        return $this->container
            ->get(ContainerBagInterface::class)
            ->get($name);
    }

    protected function getEntityManager(): EntityManagerInterface
    {
        return $this->container->get(EntityManagerInterface::class);
    }

    protected function generateUrl(
        string $route,
        array $parameters = [],
        int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH,
    ): string {
        return $this->container
            ->get(UrlGeneratorInterface::class)
            ->generate($route, $parameters, $referenceType);
    }

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

    protected function json(
        mixed $data,
        int $status = Response::HTTP_OK,
        array $headers = [],
        array $context = [],
    ): JsonResponse {
        $json = $this->container
            ->get(Serializer::class)
            ->serialize(
                $data,
                'json',
                \array_merge(['json_encode_options' => JsonResponse::DEFAULT_ENCODING_OPTIONS], $context),
            );

        return new JsonResponse($json, $status, $headers, true);
    }

    protected function file(
        \SplFileInfo|string $file,
        string $filename = null,
        string $disposition = ResponseHeaderBag::DISPOSITION_ATTACHMENT,
    ): BinaryFileResponse {
        $response = new BinaryFileResponse($file);
        $response->setContentDisposition(
            $disposition,
            null !== $filename ? $filename : $response->getFile()->getFilename(),
        );

        return $response;
    }

    protected function redirect(string $url, int $status = Response::HTTP_FOUND): RedirectResponse
    {
        return new RedirectResponse($url, $status);
    }

    protected function redirectToRoute(
        string $route,
        array $parameters = [],
        int $status = Response::HTTP_FOUND,
    ): RedirectResponse {
        return $this->redirect($this->generateUrl($route, $parameters), $status);
    }
}
