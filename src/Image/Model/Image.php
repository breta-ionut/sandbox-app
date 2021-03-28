<?php

declare(strict_types=1);

namespace App\Image\Model;

use Symfony\Component\HttpFoundation\File\File;

class Image
{
    private int $id;
    private string $path;

    /**
     * @var int Should be a IMAGETYPE_* constant.
     */
    private int $type;

    private ?File $file;
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
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @param int $type
     *
     * @return $this
     */
    public function setType(int $type): static
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasFile(): bool
    {
        return null !== $this->file;
    }

    /**
     * @return File|null
     */
    public function getFile(): ?File
    {
        return $this->file;
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
