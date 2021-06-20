<?php

declare(strict_types=1);

namespace App\Api\Http;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

class View
{
    /**
     * @param string[] $serializationGroups
     */
    public function __construct(
        private mixed $data,
        private int $status = Response::HTTP_OK,
        private array $headers = [],
        private array $serializationGroups = [],
        private array $serializationContext = [],
    ) {
    }

    public function getData(): mixed
    {
        return $this->data;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function getSerializationContext(): array
    {
        return [AbstractNormalizer::GROUPS => $this->serializationGroups] + $this->serializationContext;
    }
}
