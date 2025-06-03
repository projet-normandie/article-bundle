<?php

declare(strict_types=1);

namespace ProjetNormandie\ArticleBundle\Tests\Helper;

use Doctrine\ORM\EntityManagerInterface;
use ProjetNormandie\ArticleBundle\Entity\Article;
use ProjetNormandie\ArticleBundle\Entity\ArticleTranslation;
use ProjetNormandie\ArticleBundle\Tests\Fixtures\User;
use ProjetNormandie\ArticleBundle\ValueObject\ArticleStatus;

use ProjetNormandie\ArticleBundle\Tests\Helper\SchemaManager;

/**
 * Trait to simplify API tests
 */
trait ApiTestTrait
{
    use TestUserTrait;

    private static ?SchemaManager $schemaManager = null;

    /**
     * Gets or creates the schema manager
     */
    protected function getSchemaManager(): SchemaManager
    {
        if (self::$schemaManager === null) {
            $entityManager = static::getContainer()->get(EntityManagerInterface::class);
            self::$schemaManager = new SchemaManager($entityManager);
        }

        return self::$schemaManager;
    }

    /**
     * Ensures that the schema exists (once per test suite)
     */
    /*protected function ensureSchema(): void
    {
        $this->getSchemaManager()->ensureSchema();
    }*/

    /**
     * Creates the complete schema with Doctrine SchemaTool
     */
    protected function createSchemaWithDoctrine(): void
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $schemaTool = new \Doctrine\ORM\Tools\SchemaTool($entityManager);
        $metadata = $entityManager->getMetadataFactory()->getAllMetadata();

        // Drop the existing schema if there is one
        try {
            $schemaTool->dropSchema($metadata);
        } catch (\Exception $e) {
            // Ignore if no existing schema
        }

        // Create the new schema
        $schemaTool->createSchema($metadata);
    }

    /**
     * Cleans the data (not the schema)
     */
    protected function cleanDatabase(): void
    {
        $this->getSchemaManager()->cleanData();
    }

    /**
     * Creates the database schema for tests
     */
    protected function createSchema(): void
    {
        try {
            // Try first with SchemaTool (more reliable)
            $this->createSchemaWithDoctrine();
        } catch (\Exception $e) {
            // Fallback to manual SchemaManager
            $this->getSchemaManager()->createSchema();
        }
    }

    /**
     * Creates a test article with its translations
     */
    protected function createTestArticle(
        string $title = 'Test Article',
        string $status = ArticleStatus::PUBLISHED,
        User $author = null
    ): Article {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $author = $author ?: $this->createAndPersistTestUser();

        $article = new Article();
        $article->setStatus($status);
        $article->setAuthor($author);

        if ($status === ArticleStatus::PUBLISHED) {
            $article->setPublishedAt(new \DateTime());
        }

        // English translation
        $enTranslation = new ArticleTranslation();
        $enTranslation->setTranslatable($article);
        $enTranslation->setLocale('en');
        $enTranslation->setTitle($title);
        $enTranslation->setContent('Test content for ' . $title);

        $article->addTranslation($enTranslation);

        $entityManager->persist($article);
        $entityManager->persist($enTranslation);

        return $article;
    }

    /**
     * Creates an article with multiple translations
     */
    protected function createMultilingualTestArticle(string $baseTitle = 'Test Article'): Article
    {
        $article = $this->createTestArticle($baseTitle);
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        // French translation
        $frTranslation = new ArticleTranslation();
        $frTranslation->setTranslatable($article);
        $frTranslation->setLocale('fr');
        $frTranslation->setTitle('Article de Test');
        $frTranslation->setContent('Contenu de test pour l\'article');

        $article->addTranslation($frTranslation);
        $entityManager->persist($frTranslation);

        return $article;
    }

    /**
     * Authenticates a user for API tests
     */
    protected function loginUser(User $user = null): User
    {
        $user = $user ?: $this->createAndPersistTestUser();

        // For API Platform, we can use HTTP Basic authentication
        // or configure a JWT token according to your needs

        return $user;
    }

    /**
     * Performs a GET request with error handling
     */
    protected function getJson(string $url, array $options = []): array
    {
        $response = static::createClient()->request('GET', $url, $options);

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        return $response->toArray();
    }

    /**
     * Performs a POST request with error handling
     */
    protected function postJson(string $url, array $data, array $options = []): array
    {
        $options['json'] = $data;

        $response = static::createClient()->request('POST', $url, $options);

        return $response->toArray();
    }

    /**
     * Cleans the database after tests
     */
    /*protected function cleanDatabase(): void
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $connection = $entityManager->getConnection();

        try {
            // Désactiver les contraintes FK pour SQLite
            $connection->executeStatement('PRAGMA foreign_keys = OFF');

            // Nettoyer les tables dans l'ordre correct
            $tables = ['pna_comment', 'pna_article_translation', 'pna_article', 'test_user'];

            foreach ($tables as $table) {
                $connection->executeStatement("DELETE FROM {$table}");
                // Reset auto-increment pour SQLite
                $connection->executeStatement("DELETE FROM sqlite_sequence WHERE name = '{$table}'");
            }

            // Réactiver les contraintes FK
            $connection->executeStatement('PRAGMA foreign_keys = ON');

        } catch (\Exception $e) {
            // Si les tables n'existent pas encore, ignorer l'erreur
        }

        $entityManager->clear();
    }*/

    /**
     * Checks if tables already exist
     */
    protected function schemaExists(): bool
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $connection = $entityManager->getConnection();

        try {
            $result = $connection->executeQuery("SELECT name FROM sqlite_master WHERE type='table' AND name='pna_article'");
            return $result->fetchOne() !== false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Creates the schema only if it doesn't exist
     */
    protected function ensureSchema(): void
    {
        if (!$this->schemaExists()) {
            $this->createSchema();
        }
    }
}
