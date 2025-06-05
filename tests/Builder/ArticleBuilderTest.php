<?php

declare(strict_types=1);

namespace ProjetNormandie\ArticleBundle\Tests\Builder;

use PHPUnit\Framework\TestCase;
use ProjetNormandie\ArticleBundle\Builder\ArticleBuilder;
use ProjetNormandie\ArticleBundle\Entity\Article;
use ProjetNormandie\ArticleBundle\Tests\Fixtures\User;
use ProjetNormandie\ArticleBundle\Enum\ArticleStatus;
use Doctrine\ORM\EntityManagerInterface;
use ProjetNormandie\ArticleBundle\Tests\Helper\TestUserTrait;

class ArticleBuilderTest extends TestCase
{
    use TestUserTrait;
    private ArticleBuilder $builder;
    private EntityManagerInterface $entityManager;
    private User $user;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        // Create a test user with the trait
        $this->user = $this->createTestUser(1, 'testuser');

        $this->builder = new ArticleBuilder($this->entityManager);
    }

    public function testSetAuthor(): void
    {
        $result = $this->builder->setAuthor($this->user);

        $this->assertSame($this->builder, $result); // Fluent interface
    }

    public function testSetTitle(): void
    {
        $result = $this->builder->setTitle('English Title', 'en');

        $this->assertSame($this->builder, $result); // Fluent interface
    }

    public function testSetContent(): void
    {
        $result = $this->builder->setContent('English Content', 'en');

        $this->assertSame($this->builder, $result); // Fluent interface
    }

    public function testFluentInterface(): void
    {
        $result = $this->builder
            ->setAuthor($this->user)
            ->setTitle('Test Title', 'en')
            ->setContent('Test Content', 'en');

        $this->assertSame($this->builder, $result);
    }

    public function testSendCreatesAndPersistsArticle(): void
    {
        $this->entityManager->expects($this->exactly(1)) // Article + 2 translations
        ->method('persist');

        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->builder
            ->setAuthor($this->user)
            ->setTitle('English Title', 'en')
            ->setTitle('Titre Français', 'fr')
            ->setContent('English Content', 'en')
            ->setContent('Contenu Français', 'fr')
            ->send();

        // If we reach here without exception, the test passes
        $this->assertTrue(true);
    }

    public function testSendSetsCorrectArticleProperties(): void
    {
        $capturedArticle = null;

        $this->entityManager->expects($this->exactly(1))
            ->method('persist')
            ->willReturnCallback(function ($entity) use (&$capturedArticle) {
                if ($entity instanceof Article) {
                    $capturedArticle = $entity;
                }
            });

        $this->entityManager->expects($this->once())
            ->method('flush');

        $this->builder
            ->setAuthor($this->user)
            ->setTitle('Test Title', 'en')
            ->setContent('Test Content', 'en')
            ->send();

        $this->assertNotNull($capturedArticle);
        $this->assertSame($this->user, $capturedArticle->getAuthor());
        $this->assertEquals(ArticleStatus::PUBLISHED, $capturedArticle->getStatus());
        $this->assertInstanceOf(\DateTime::class, $capturedArticle->getPublishedAt());
        $this->assertEquals('Test Title', $capturedArticle->getTitle('en'));
        $this->assertEquals('Test Content', $capturedArticle->getContent('en'));
    }

    public function testSendWithMultipleLanguages(): void
    {
        $capturedArticle = null;

        $this->entityManager->expects($this->exactly(1)) // Article + 2 translations
        ->method('persist')
            ->willReturnCallback(function ($entity) use (&$capturedArticle) {
                if ($entity instanceof Article) {
                    $capturedArticle = $entity;
                }
            });

        $this->builder
            ->setAuthor($this->user)
            ->setTitle('English Title', 'en')
            ->setTitle('Titre Français', 'fr')
            ->setContent('English Content', 'en')
            ->setContent('Contenu Français', 'fr')
            ->send();

        $this->assertNotNull($capturedArticle);
        $this->assertEquals('English Title', $capturedArticle->getTitle('en'));
        $this->assertEquals('Titre Français', $capturedArticle->getTitle('fr'));
        $this->assertEquals('English Content', $capturedArticle->getContent('en'));
        $this->assertEquals('Contenu Français', $capturedArticle->getContent('fr'));
    }

    public function testBuilderCanBeUsedMultipleTimes(): void
    {
        $this->entityManager->expects($this->exactly(2)) // 2 articles + 2 translations
        ->method('persist');

        $this->entityManager->expects($this->exactly(2))
            ->method('flush');

        // First article
        $this->builder
            ->setAuthor($this->user)
            ->setTitle('First Article', 'en')
            ->setContent('First Content', 'en')
            ->send();

        // Second article
        $this->builder
            ->setAuthor($this->user)
            ->setTitle('Second Article', 'en')
            ->setContent('Second Content', 'en')
            ->send();

        $this->assertTrue(true); // If we reach here, both articles have been created
    }
}
