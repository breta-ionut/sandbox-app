<?php

declare(strict_types=1);

namespace App\User\Model;

use App\Common\Validator\UniqueEntity;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;
use Symfony\Component\Security\Core\User\LegacyPasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

#[UniqueEntity('email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface, LegacyPasswordAuthenticatedUserInterface
{
    private const ROLE_DEFAULT = 'ROLE_USER';

    #[Groups('api_response')]
    private int $id;

    #[Groups(['api_request', 'api_response'])]

    #[NotBlank]
    #[Length(max: 50)]
    private string $firstName;

    #[Groups(['api_request', 'api_response'])]

    #[NotBlank]
    #[Length(max: 50)]
    private string $lastName;

    #[Groups(['api_request', 'api_response'])]

    #[NotBlank]
    #[Length(max: 255)]
    #[Email]
    private string $email;

    /**
     * @var string[]
     */
    private array $roles = [self::ROLE_DEFAULT];

    #[Groups('api_request')]

    #[NotBlank]
    #[Length(min: 8, max: PasswordHasherInterface::MAX_PASSWORD_LENGTH)]
    private string $plainPassword;

    private string $password;

    #[Groups('api_response')]
    private Token $currentToken;

    public function getId(): int
    {
        return $this->id;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * @return $this
     */
    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    /**
     * @return $this
     */
    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @return $this
     */
    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * @return $this
     */
    public function addRole(string $role): static
    {
        if (!\in_array($role, $this->roles, true)) {
            $this->roles[] = $role;
        }

        return $this;
    }

    /**
     * @return $this
     *
     * @throws \InvalidArgumentException
     */
    public function removeRole(string $role): static
    {
        if ($role === self::ROLE_DEFAULT) {
            throw new \InvalidArgumentException(\sprintf(
                'Cannot remove default role "%s" from user.',
                self::ROLE_DEFAULT
            ));
        }

        $key = \array_search($role, $this->roles, true);
        if (false !== $key) {
            unset($this->roles[$key]);

            $this->roles = \array_values($this->roles);
        }

        return $this;
    }

    public function getPlainPassword(): string
    {
        return $this->plainPassword;
    }

    /**
     * @return $this
     */
    public function setPlainPassword(string $plainPassword): static
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
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

    public function getCurrentToken(): Token
    {
        return $this->currentToken;
    }

    /**
     * @return $this
     */
    public function setCurrentToken(Token $currentToken): static
    {
        $this->currentToken = $currentToken;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function eraseCredentials(): void
    {
        unset($this->plainPassword);
    }

    /**
     * {@inheritDoc}
     */
    public function getUsername(): string
    {
        return $this->getUserIdentifier();
    }

    /**
     * {@inheritDoc}
     */
    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    /**
     * {@inheritDoc}
     */
    public function getSalt(): ?string
    {
        return null;
    }
}
