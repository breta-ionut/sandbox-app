<?php

declare(strict_types=1);

namespace App\User\Model;

class Token
{
    private const TOKEN_SIZE = 64;

    private int $id;
    private string $token;
    private \DateTimeInterface $expiresAt;
    private User $user;

    public function __construct()
    {
        $this->token = \bin2hex(\random_bytes(self::TOKEN_SIZE));
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
     * @return \DateTimeInterface
     */
    public function getExpiresAt(): \DateTimeInterface
    {
        return $this->expiresAt;
    }

    /**
     * @param \DateTimeInterface $expiresAt
     *
     * @return $this
     */
    public function setExpiresAt(\DateTimeInterface $expiresAt): static
    {
        $this->expiresAt = $expiresAt;

        return $this;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param User $user
     *
     * @return $this
     */
    public function setUser(User $user): static
    {
        $this->user = $user;

        return $this;
    }
}
