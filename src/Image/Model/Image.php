<?php

declare(strict_types=1);

namespace App\Image\Model;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints\Image as ImageConstraint;
use Symfony\Component\Validator\Constraints\NotNull;

class Image
{
    private const TOKEN_SIZE = 16;

    #[Groups(['api_request', 'api_response'])]
    private int $id;

    private string $token;
    private string $path;
    private \DateTime $createdAt;

    #[NotNull]
    #[ImageConstraint(maxSize: '16M', detectCorrupted: true)]
    private ?File $file;

    private ?ImageContent $content;

    #[Groups('api_response')]
    private ?string $originalPublicUrl = null;

    /**
     * @var array<string, string>
     */
    #[Groups('api_response')]
    private array $publicUrlsPerStyles = [];

    public function __construct(?File $file)
    {
        $this->file = $file;
        $this->token = \bin2hex(\random_bytes(self::TOKEN_SIZE));
        $this->createdAt = new \DateTime();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return $this
     */
    public function setPath(string $path): static
    {
        $this->path = $path;

        return $this;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function getFile(): ?File
    {
        return $this->file;
    }

    /**
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

        return $this->content = new ImageContent($this->file->getContent());
    }

    public function getOriginalPublicUrl(): ?string
    {
        return $this->originalPublicUrl;
    }

    /**
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
     * @return $this
     */
    public function setPublicUrlForStyle(string $style, string $publicUrl): static
    {
        $this->publicUrlsPerStyles[$style] = $publicUrl;

        return $this;
    }
}
