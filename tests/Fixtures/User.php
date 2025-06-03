<?php

declare(strict_types=1);

namespace ProjetNormandie\ArticleBundle\Tests\Fixtures;

use Doctrine\ORM\Mapping as ORM;
use ProjetNormandie\ArticleBundle\Entity\UserInterface;
use Symfony\Component\Security\Core\User\UserInterface as SymfonyUserInterface;

/**
 * User entity specific for tests
 * Implements necessary interfaces without depending on other bundles
 */
#[ORM\Entity]
#[ORM\Table(name: 'test_user')]
class User implements UserInterface, SymfonyUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    private string $username = '';

    #[ORM\Column(type: 'string', length: 255)]
    private string $email = '';

    #[ORM\Column(type: 'json')]
    private array $roles = ['ROLE_USER'];

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $password = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;
        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): self
    {
        $this->password = $password;
        return $this;
    }

    /**
     * Méthodes requises par SymfonyUserInterface
     */
    public function getUserIdentifier(): string
    {
        return $this->username;
    }

    public function eraseCredentials(): void
    {
        // If you store any temporary sensitive data on the user, clear it here
    }

    public function __toString(): string
    {
        return $this->username;
    }

    // Méthodes utilitaires pour les tests
    public static function createForTest(int $id = 1, string $username = 'testuser', string $email = null): self
    {
        $user = new self();
        $user->setId($id);
        $user->setUsername($username);
        $user->setEmail($email ?: $username . '@example.com');

        return $user;
    }
}
