<?php

declare(strict_types=1);

namespace App\Api\Http;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;

class ResponseFactory
{
    private SerializerInterface $serializer;

    /**
     * @param SerializerInterface $serializer
     */
    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @param mixed $data
     * @param int   $status
     * @param array $headers
     * @param array $context
     *
     * @return JsonResponse
     */
    public function createFromData(
        $data,
        int $status = Response::HTTP_OK,
        array $headers = [],
        array $context = []
    ): JsonResponse {
        $json = $this->serializer->serialize($data, 'json', $this->setContextDefaults($context));

        return new JsonResponse($json, $status, $headers, true);
    }

    /**
     * @param \Throwable $exception
     * @param int        $status
     * @param array      $headers
     * @param array      $context
     *
     * @return JsonResponse
     */
    public function createFromThrowable(
        \Throwable $exception,
        int $status = Response::HTTP_OK,
        array $headers = [],
        array $context = []
    ): JsonResponse {
        $json = $this->serializer->serialize($exception, 'json', $this->setContextDefaults($context));
        $headers = \array_merge(['Content-Type' => 'application/problem+json'], $headers);

        return new JsonResponse($json, $status, $headers, true);
    }

    /**
     * @param array $context
     *
     * @return array
     */
    private function setContextDefaults(array $context): array
    {
        return \array_merge(
            ['api_response' => true, 'json_encode_options' => JsonResponse::DEFAULT_ENCODING_OPTIONS],
            $context
        );
    }
}
