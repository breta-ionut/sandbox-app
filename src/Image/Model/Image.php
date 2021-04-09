<?php

declare(strict_types=1);

namespace App\Image\Model;

use Symfony\Component\HttpFoundation\File\File;

class Image
{
    private const TOKEN_SIZE = 16;

    private int $id;
    private string $token;
    private string $path;
    private \DateTime $createdAt;
    private ?File $file;
    private ?ImageContent $content;
    private ?string $originalPublicUrl;

    /**
     * @var array<string, string>
     */
    private array $publicUrlsPerStyles = [];

    /**
     * @param File|null $file
     */
    public function __construct(?File $file)
    {
        $this->file = $file;
        $this->token = \bin2hex(\random_bytes(self::TOKEN_SIZE));
        $this->createdAt = new \DateTime();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @param string $path
     *
     * @return $this
     */
    public function setPath(string $path): static
    {
        $this->path = $path;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    /**
     * @return File|null
     */
    public function getFile(): ?File
    {
        return $this->file;
    }

    /**
     * @return ImageContent
     *
     * @throws \LogicException
     */
    public function getContent(): ImageContent
    {
        if (isset($this->content)) {
            return $this->content;
        }

        if (!isset($this->file)) {
            throw new \LogicException('Cannot determine the image content since the image file is missing.');
        }

        return $this->content = ImageContent::fromFile($this->file);
    }

    /**
     * @return string|null
     */
    public function getOriginalPublicUrl(): ?string
    {
        return $this->originalPublicUrl;
    }

    /**
     * @param string $originalPublicUrl
     *
     * @return $this
     */
    public function setOriginalPublicUrl(string $originalPublicUrl): static
    {
        $this->originalPublicUrl = $originalPublicUrl;

        return $this;
    }

    /**
     * @return array<string, string>
     */
    public function getPublicUrlsPerStyles(): array
    {
        return $this->publicUrlsPerStyles;
    }

    /**
     * @param string $style
     *
     * @return string
     *
     * @throws \RangeException
     */
    public function getPublicUrlForStyle(string $style): string
    {
        if (!isset($this->publicUrlsPerStyles[$style])) {
            throw new \RangeException(\sprintf('No public URL available for style "%s".', $style));
        }

        return $this->publicUrlsPerStyles[$style];
    }

    /**
     * @param string $style
     * @param string $publicUrl
     *
     * @return $this
     */
    public function setPublicUrlForStyle(string $style, string $publicUrl): static
    {
        $this->publicUrlsPerStyles[$style] = $publicUrl;

        return $this;
    }
}
