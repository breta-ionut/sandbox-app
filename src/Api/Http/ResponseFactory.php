<?php

declare(strict_types=1);

namespace App\Api\Http;

use App\Api\Error\Problem;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

class ResponseFactory
{
    private const DEFAULT_SERIALIZATION_GROUPS = ['api_response'];

    public function __construct(private SerializerInterface $serializer)
    {
    }

    public function createFromException(
        \Throwable $exception,
        int $status = Response::HTTP_INTERNAL_SERVER_ERROR,
        array $headers = [],
        array $context = [],
    ): JsonResponse {
        $problem = (new Problem())
            ->setStatus($status)
            ->setHeaders($headers)
            ->fromException($exception);

        return $this->createFromProblem($problem, $context);
    }

    public function createFromProblem(Problem $problem, array $context = []): JsonResponse
    {
        $headers = $problem->getHeaders() + ['Content-Type' => 'application/problem+json'];

        return $this->createFromData($problem, $problem->getStatus(), $headers, $context);
    }

    public function createFromData(
        mixed $data,
        int $status = Response::HTTP_OK,
        array $headers = [],
        array $context = [],
    ): JsonResponse {
        if (null !== $data) {
            $json = $this->serializer->serialize($data, 'json', $this->setContextDefaults($context));
        } else {
            $json = null;
        }

        return new JsonResponse($json, $status, $headers, null !== $json);
    }

    private function setContextDefaults(array $context): array
    {
        $context[AbstractNormalizer::GROUPS] = \array_merge(
            $context[AbstractNormalizer::GROUPS] ?? [],
            self::DEFAULT_SERIALIZATION_GROUPS,
        );

        return \array_merge(
            ['json_encode_options' => JsonResponse::DEFAULT_ENCODING_OPTIONS, 'api_response' => true],
            $context,
        );
    }
}
