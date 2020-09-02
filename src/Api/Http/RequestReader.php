<?php

declare(strict_types=1);

namespace App\Api\Http;

use App\Api\Exception\MalformedInputException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;

class RequestReader
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
     * @param Request $request
     * @param string  $type
     * @param array   $context
     *
     * @return mixed
     *
     * @throws MalformedInputException
     */
    public function read(Request $request, string $type, array $context = [])
    {
        try {
            return $this->serializer->deserialize(
                $request->getContent(),
                $type,
                'json',
                $this->setContextDefaults($context)
            );
        } catch (\Throwable $exception) {
            throw new MalformedInputException(0, $exception);
        }
    }

    /**
     * @param array $context
     *
     * @return array
     */
    private function setContextDefaults(array $context): array
    {
        return \array_merge(['api_request' => true], $context);
    }
}
