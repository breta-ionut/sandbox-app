<?php

declare(strict_types=1);

namespace App\User\Model;

use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class Credentials
{
    #[Groups('api_request')]

    #[NotBlank]
    #[Length(max: 255)]
    #[Email]
    private string $username;

    #[Groups('api_request')]

    #[NotBlank]
    private string $password;

    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @return $this
     */
    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @return $this
     */
    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }
}
