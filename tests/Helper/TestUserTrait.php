<?php

declare(strict_types=1);

namespace ProjetNormandie\ArticleBundle\Tests\Helper;

use ProjetNormandie\ArticleBundle\Tests\Fixtures\User;

/**
 * Trait to easily create test users
 * Uses the test-specific User entity (no external dependency)
 */
trait TestUserTrait
{
    /**
     * Creates a test user with default values
     */
    protected function createTestUser(int $id = 1, string $username = 'testuser', string $email = null): User
    {
        return User::createForTest($id, $username, $email);
    }

    /**
     * Creates multiple test users
     */
    protected function createTestUsers(int $count = 3): array
    {
        $users = [];
        for ($i = 1; $i <= $count; $i++) {
            $users[] = $this->createTestUser($i, "user{$i}", "user{$i}@example.com");
        }
        return $users;
    }

    /**
     * Creates a user and persists it to database (for integration tests)
     */
    protected function createAndPersistTestUser(int $id = null, string $username = 'testuser'): User
    {
        $user = new User();
        $user->setUsername($username);
        $user->setEmail($username . '@example.com');

        if ($id !== null) {
            $user->setId($id);
        }

        // If we have access to EntityManager
        if (property_exists($this, 'entityManager') && $this->entityManager) {
            $this->entityManager->persist($user);
            // Note: flush() must be called explicitly by the test
        }

        return $user;
    }

    /**
     * Creates a user with specific roles
     */
    protected function createTestUserWithRoles(array $roles, int $id = 1, string $username = 'testuser'): User
    {
        $user = $this->createTestUser($id, $username);
        $user->setRoles($roles);

        return $user;
    }

    /**
     * Creates a test admin
     */
    protected function createTestAdmin(int $id = 1, string $username = 'admin'): User
    {
        return $this->createTestUserWithRoles(['ROLE_ADMIN'], $id, $username);
    }
}
