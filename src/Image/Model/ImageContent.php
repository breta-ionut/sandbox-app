<?php

declare(strict_types=1);

namespace App\Image\Model;

class ImageContent
{
    /**
     * @var resource|string
     */
    private mixed $content;

    /**
     * @var int Should be a IMAGETYPE_* constant.
     */
    private int $type;

    /**
     * @param resource|string $content
     * @param int             $type
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(mixed $content, int $type)
    {
        if (!\is_resource($content) || !\is_string($content)) {
            throw new \InvalidArgumentException('The image content must be a resource (stream) or a string.');
        }

        $this->content = $content;
        $this->type = $type;
    }

    /**
     * @return resource|string
     */
    public function reveal(): mixed
    {
        return $this->content;
    }

    /**
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }
}
