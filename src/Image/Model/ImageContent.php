<?php

declare(strict_types=1);

namespace App\Image\Model;

class ImageContent implements \Stringable
{
    private string $content;

    /**
     * @var int Should be a \IMAGETYPE_* constant other than \IMAGETYPE_UNKNOWN.
     */
    private int $type;

    /**
     * @throws \UnexpectedValueException
     */
    public function __construct(string $content)
    {
        $info = @\getimagesizefromstring($content);

        if (!isset($info[2]) || \IMAGETYPE_UNKNOWN === $info[2]) {
            throw new \UnexpectedValueException('Image type could not be detected, perhaps the image isn\'t valid.');
        }

        $this->content = $content;
        $this->type = $info[2];
    }

    public function getFormat(): string
    {
        return \image_type_to_extension($this->type, false);
    }

    /**
     * {@inheritDoc}
     */
    public function __toString(): string
    {
        return $this->content;
    }
}
