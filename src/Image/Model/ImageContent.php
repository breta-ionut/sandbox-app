<?php

declare(strict_types=1);

namespace App\Image\Model;

use Symfony\Component\HttpFoundation\File\File;

class ImageContent implements \Stringable
{
    private string $content;

    /**
     * @var int Should be a \IMAGETYPE_* constant other than \IMAGETYPE_UNKNOWN.
     */
    private int $type;

    /**
     * @param string $content
     *
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

    /**
     * @param File $file
     *
     * @return static
     */
    public static function fromFile(File $file): static
    {
        return new static($file->getContent());
    }

    /**
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * {@inheritDoc}
     */
    public function __toString(): string
    {
        return $this->content;
    }
}
