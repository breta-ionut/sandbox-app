<?php

declare(strict_types=1);

namespace App\User\Model;

use Symfony\Component\Security\Core\User\UserInterface;

class User implements UserInterface
{
    private const ROLE_DEFAULT = 'ROLE_USER';

    private int $id;
    private string $firstName;
    private string $lastName;
    private string $email;
    private array $roles = [self::ROLE_DEFAULT];
    private string $plainPassword;
    private string $password;
    private Token $currentToken;

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
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     *
     * @return $this
     */
    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * @return string
     */
    public function getLastName(): string
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     *
     * @return $this
     */
    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     *
     * @return $this
     */
    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * @param string $role
     *
     * @return $this
     */
    public function addRole(string $role): self
    {
        if (!\in_array($role, $this->roles, true)) {
            $this->roles[] = $role;
        }

        return $this;
    }

    /**
     * @param string $role
     *
     * @return $this
     *
     * @throws \InvalidArgumentException
     */
    public function removeRole(string $role): self
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

    /**
     * @return string
     */
    public function getPlainPassword(): string
    {
        return $this->plainPassword;
    }

    /**
     * @param string $plainPassword
     *
     * @return $this
     */
    public function setPlainPassword(string $plainPassword): self
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $password
     *
     * @return $this
     */
    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return Token
     */
    public function getCurrentToken(): Token
    {
        return $this->currentToken;
    }

    /**
     * @param Token $currentToken
     *
     * @return $this
     */
    public function setCurrentToken(Token $currentToken): self
    {
        $this->currentToken = $currentToken;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function getUsername()
    {
        return $this->email;
    }

    /**
     * {@inheritDoc}
     */
    public function eraseCredentials()
    {
        unset($this->plainPassword);
    }
}
