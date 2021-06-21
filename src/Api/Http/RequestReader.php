<?php

declare(strict_types=1);

namespace App\Api\Http;

use App\Api\Exception\MalformedInputException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

class RequestReader
{
    private const DEFAULT_DESERIALIZATION_GROUPS = ['api_request'];

    public function __construct(private SerializerInterface $serializer)
    {
    }

    /**
     * @throws MalformedInputException
     */
    public function read(Request $request, string $type, array $context = []): mixed
    {
        try {
            return $this->serializer->deserialize(
                $request->getContent(),
                $type,
                'json',
                $this->setContextDefaults($context),
            );
        } catch (ExceptionInterface $exception) {
            throw new MalformedInputException(0, $exception);
        }
    }

    private function setContextDefaults(array $context): array
    {
        $context[AbstractNormalizer::GROUPS] = \array_merge(
            $context[AbstractNormalizer::GROUPS] ?? [],
            self::DEFAULT_DESERIALIZATION_GROUPS,
        );

        return \array_merge(['api_request' => true], $context);
    }
}
