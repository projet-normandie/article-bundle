<?php

declare(strict_types=1);

namespace ProjetNormandie\ArticleBundle\Tests\Helper;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;

/**
 * Specialized schema manager for bundle tests
 * Handles table creation/deletion without depending on Symfony Console
 */
class SchemaManager
{
    private EntityManagerInterface $entityManager;
    private Connection $connection;
    private static bool $schemaCreated = false;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->connection = $entityManager->getConnection();
    }

    /**
     * Creates the schema only once per test suite
     */
    public function ensureSchema(): void
    {
        if (self::$schemaCreated) {
            return;
        }

        $this->createSchema();
        self::$schemaCreated = true;
    }

    /**
     * Creates the database schema
     */
    public function createSchema(): void
    {
        try {
            // Try first with SchemaTool (recommended)
            $this->createSchemaWithSchemaTool();
        } catch (\Exception $e) {
            // Fallback: manual creation for SQLite
            $this->createSchemaManually();
        }
    }

    /**
     * Creation with Doctrine SchemaTool
     */
    private function createSchemaWithSchemaTool(): void
    {
        $schemaTool = new SchemaTool($this->entityManager);
        $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();

        // Drop and recreate
        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);
    }

    /**
     * Manual creation for SQLite (fallback)
     */
    private function createSchemaManually(): void
    {
        $queries = $this->getSQLiteSchemaQueries();

        foreach ($queries as $query) {
            $this->connection->executeStatement($query);
        }
    }

    /**
     * Generates SQL queries for SQLite
     */
    private function getSQLiteSchemaQueries(): array
    {
        return [
            // Test User table
            'CREATE TABLE IF NOT EXISTS test_user (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username VARCHAR(180) NOT NULL UNIQUE,
                email VARCHAR(255) NOT NULL,
                roles TEXT NOT NULL DEFAULT \'["ROLE_USER"]\',
                password VARCHAR(255) DEFAULT NULL
            )',

            // Article table
            'CREATE TABLE IF NOT EXISTS pna_article (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                author_id INTEGER NOT NULL,
                status VARCHAR(30) NOT NULL DEFAULT "UNDER CONSTRUCTION",
                nb_comment INTEGER NOT NULL DEFAULT 0,
                published_at DATETIME DEFAULT NULL,
                slug VARCHAR(255) NOT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (author_id) REFERENCES test_user (id)
            )',

            // ArticleTranslation table
            'CREATE TABLE IF NOT EXISTS pna_article_translation (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                translatable_id INTEGER NOT NULL,
                locale VARCHAR(5) NOT NULL,
                title VARCHAR(255) NOT NULL,
                content TEXT NOT NULL,
                FOREIGN KEY (translatable_id) REFERENCES pna_article (id) ON DELETE CASCADE,
                UNIQUE(translatable_id, locale)
            )',

            // Comment table
            'CREATE TABLE IF NOT EXISTS pna_comment (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                article_id INTEGER NOT NULL,
                user_id INTEGER NOT NULL,
                content TEXT NOT NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (article_id) REFERENCES pna_article (id),
                FOREIGN KEY (user_id) REFERENCES test_user (id)
            )',

            // Indexes to optimize queries
            'CREATE INDEX IF NOT EXISTS idx_article_status ON pna_article (status)',
            'CREATE INDEX IF NOT EXISTS idx_article_published_at ON pna_article (published_at)',
            'CREATE INDEX IF NOT EXISTS idx_article_translation_locale ON pna_article_translation (locale)',
            'CREATE INDEX IF NOT EXISTS idx_comment_article ON pna_comment (article_id)',
        ];
    }

    /**
     * Cleans all data
     */
    public function cleanData(): void
    {
        try {
            // Disable FK constraints for SQLite
            $this->connection->executeStatement('PRAGMA foreign_keys = OFF');

            // Clean in reverse order of dependencies
            $tables = ['pna_comment', 'pna_article_translation', 'pna_article', 'test_user'];

            foreach ($tables as $table) {
                $this->connection->executeStatement("DELETE FROM {$table}");
                // Reset auto-increment for SQLite
                $this->connection->executeStatement("DELETE FROM sqlite_sequence WHERE name = '{$table}'");
            }

            // Re-enable FK constraints
            $this->connection->executeStatement('PRAGMA foreign_keys = ON');

        } catch (\Exception $e) {
            // Ignore if tables don't exist
        }

        $this->entityManager->clear();
    }

    /**
     * Completely drops the schema
     */
    public function dropSchema(): void
    {
        try {
            $schemaTool = new SchemaTool($this->entityManager);
            $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();
            $schemaTool->dropSchema($metadata);
        } catch (\Exception $e) {
            // Manual fallback
            $tables = ['pna_comment', 'pna_article_translation', 'pna_article', 'test_user'];
            foreach ($tables as $table) {
                try {
                    $this->connection->executeStatement("DROP TABLE IF EXISTS {$table}");
                } catch (\Exception $dropException) {
                    // Ignore drop errors
                }
            }
        }

        self::$schemaCreated = false;
    }

    /**
     * Checks if the schema exists
     */
    public function schemaExists(): bool
    {
        try {
            $result = $this->connection->executeQuery(
                "SELECT name FROM sqlite_master WHERE type='table' AND name='pna_article'"
            );
            return $result->fetchOne() !== false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Returns database statistics
     */
    public function getStats(): array
    {
        if (!$this->schemaExists()) {
            return ['schema_exists' => false];
        }

        $stats = ['schema_exists' => true];

        $tables = ['test_user', 'pna_article', 'pna_article_translation', 'pna_comment'];

        foreach ($tables as $table) {
            try {
                $result = $this->connection->executeQuery("SELECT COUNT(*) FROM {$table}");
                $stats[$table] = (int) $result->fetchOne();
            } catch (\Exception $e) {
                $stats[$table] = 'error';
            }
        }

        return $stats;
    }
}
