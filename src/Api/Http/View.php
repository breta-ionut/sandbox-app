<?php

declare(strict_types=1);

namespace App\Api\Http;

use Symfony\Component\HttpFoundation\Response;

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

    /**
     * @param mixed    $data
     * @param int      $status
     * @param array    $headers
     * @param string[] $serializationGroups
     */
    public function __construct(
        mixed $data,
        int $status = Response::HTTP_OK,
        array $headers = [],
        array $serializationGroups = []
    ) {
        $this->data = $data;
        $this->status = $status;
        $this->headers = $headers;
        $this->serializationGroups = $serializationGroups;
    }

    /**
     * @return mixed
     */
    public function getData()
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
     * @return string[]
     */
    public function getSerializationGroups(): array
    {
        return $this->serializationGroups;
    }
}
