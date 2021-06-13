<?php

declare(strict_types=1);

namespace App\User\Model;

use Symfony\Component\Serializer\Annotation\Groups;

class Token
{
    private const TOKEN_SIZE = 64;

    private int $id;

    #[Groups('api_response')]
    private string $token;

    #[Groups('api_response')]
    private \DateTimeInterface $expiresAt;

    private User $user;

    public function __construct()
    {
        $this->token = \bin2hex(\random_bytes(self::TOKEN_SIZE));
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getExpiresAt(): \DateTimeInterface
    {
        return $this->expiresAt;
    }

    /**
     * @return $this
     */
    public function setExpiresAt(\DateTimeInterface $expiresAt): static
    {
        $this->expiresAt = $expiresAt;

        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @return $this
     */
    public function setUser(User $user): static
    {
        $this->user = $user;

        return $this;
    }
}
