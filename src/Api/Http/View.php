<?php

declare(strict_types=1);

namespace App\Api\Http;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

class View
{
    /**
     * @var mixed
     */
    private $data;

    private int $status;
    private array $headers;

    /**
     * @var string[]
     */
    private array $serializationGroups;

    private array $serializationContext;

    /**
     * @param mixed    $data
     * @param int      $status
     * @param array    $headers
     * @param string[] $serializationGroups
     * @param array    $serializationContext
     */
    public function __construct(
        mixed $data,
        int $status = Response::HTTP_OK,
        array $headers = [],
        array $serializationGroups = [],
        array $serializationContext = [],
    ) {
        $this->data = $data;
        $this->status = $status;
        $this->headers = $headers;
        $this->serializationGroups = $serializationGroups;
        $this->serializationContext = $serializationContext;
    }

    /**
     * @return mixed
     */
    public function getData(): mixed
    {
        return $this->data;
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @return array
     */
    public function getSerializationContext(): array
    {
        return [AbstractNormalizer::GROUPS => $this->serializationGroups] + $this->serializationContext;
    }
}
