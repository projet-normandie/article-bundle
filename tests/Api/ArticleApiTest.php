<?php

declare(strict_types=1);

namespace ProjetNormandie\ArticleBundle\Tests\Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use Doctrine\ORM\EntityManagerInterface;
use ProjetNormandie\ArticleBundle\Entity\Article;
use ProjetNormandie\ArticleBundle\Entity\ArticleTranslation;
use ProjetNormandie\ArticleBundle\Tests\Fixtures\User;
use ProjetNormandie\ArticleBundle\ValueObject\ArticleStatus;
use PHPUnit\Framework\Attributes\Group;
use ProjetNormandie\ArticleBundle\Tests\Helper\ApiTestTrait;

#[Group('api')]
#[Group('integration')]
class ArticleApiTest extends ApiTestCase
{
    use ApiTestTrait;

    private EntityManagerInterface $entityManager;
    private User $testUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure that the schema exists
        $this->ensureSchema();

        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);

        // Create and persist a real test user
        $this->testUser = $this->createAndPersistTestUser(1, 'testuser');
        $this->entityManager->flush();
    }

    protected function tearDown(): void
    {
        $this->cleanDatabase();
        parent::tearDown();
    }

    public function testGetArticleCollection(): void
    {
        // Create test articles
        $article1 = $this->createTestArticle('Article 1', ArticleStatus::PUBLISHED);
        $article2 = $this->createTestArticle('Article 2', ArticleStatus::PUBLISHED);

        $this->entityManager->flush();

        $response = static::createClient()->request('GET', '/api/articles');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $data = $response->toArray();
        $this->assertArrayHasKey('hydra:member', $data);
        $this->assertCount(2, $data['hydra:member']);

        // Check the data structure
        $firstArticle = $data['hydra:member'][0];
        $this->assertArrayHasKey('id', $firstArticle);
        $this->assertArrayHasKey('title', $firstArticle);
        $this->assertArrayHasKey('content', $firstArticle);
        $this->assertArrayHasKey('createdAt', $firstArticle);
        $this->assertArrayHasKey('updatedAt', $firstArticle);
    }

    public function testGetSingleArticle(): void
    {
        $article = $this->createTestArticle('Test Article', ArticleStatus::PUBLISHED);
        $this->entityManager->flush();

        $response = static::createClient()->request('GET', '/api/articles/' . $article->getId());

        $this->assertResponseIsSuccessful();

        $data = $response->toArray();
        $this->assertEquals($article->getId(), $data['id']);
        $this->assertEquals('Test Article', $data['title']);
        $this->assertArrayHasKey('content', $data);
    }

    public function testGetArticleWithDifferentLocales(): void
    {
        $article = $this->createTestArticle('English Title', ArticleStatus::PUBLISHED);

        // Add a French translation
        $frTranslation = new ArticleTranslation();
        $frTranslation->setTranslatable($article);
        $frTranslation->setLocale('fr');
        $frTranslation->setTitle('Titre Français');
        $frTranslation->setContent('Contenu en français');

        $article->addTranslation($frTranslation);
        $this->entityManager->persist($frTranslation);
        $this->entityManager->flush();

        // Test with French locale
        $client = static::createClient();
        $response = $client->request('GET', '/api/articles/' . $article->getId(), [
            'headers' => ['Accept-Language' => 'fr']
        ]);

        $this->assertResponseIsSuccessful();

        $data = $response->toArray();
        $this->assertEquals('Titre Français', $data['title']);
        $this->assertEquals('Contenu en français', $data['content']);

        // Test with English locale
        $response = $client->request('GET', '/api/articles/' . $article->getId(), [
            'headers' => ['Accept-Language' => 'en']
        ]);

        $data = $response->toArray();
        $this->assertEquals('English Title', $data['title']);
    }

    public function testFilterArticlesByStatus(): void
    {
        $publishedArticle = $this->createTestArticle('Published Article', ArticleStatus::PUBLISHED);
        $draftArticle = $this->createTestArticle('Draft Article', ArticleStatus::UNDER_CONSTRUCTION);

        $this->entityManager->flush();

        // Test filter on published articles
        $response = static::createClient()->request('GET', '/api/articles?status=' . ArticleStatus::PUBLISHED);

        $this->assertResponseIsSuccessful();

        $data = $response->toArray();
        $this->assertCount(1, $data['hydra:member']);
        $this->assertEquals($publishedArticle->getId(), $data['hydra:member'][0]['id']);

        // Test filter on drafts
        $response = static::createClient()->request('GET', '/api/articles?status=' . ArticleStatus::UNDER_CONSTRUCTION);

        $data = $response->toArray();
        $this->assertCount(1, $data['hydra:member']);
        $this->assertEquals($draftArticle->getId(), $data['hydra:member'][0]['id']);
    }

    public function testGetNonExistentArticle(): void
    {
        static::createClient()->request('GET', '/api/articles/99999');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testArticleOrderingByPublishedDate(): void
    {
        $oldDate = new \DateTime('-2 days');
        $newDate = new \DateTime('-1 day');

        $olderArticle = $this->createTestArticle('Older Article', ArticleStatus::PUBLISHED);
        $olderArticle->setPublishedAt($oldDate);

        $newerArticle = $this->createTestArticle('Newer Article', ArticleStatus::PUBLISHED);
        $newerArticle->setPublishedAt($newDate);

        $this->entityManager->flush();

        $response = static::createClient()->request('GET', '/api/articles');

        $this->assertResponseIsSuccessful();

        $data = $response->toArray();

        // Vérifier que les articles sont triés par date de publication DESC
        $this->assertEquals($newerArticle->getId(), $data['hydra:member'][0]['id']);
        $this->assertEquals($olderArticle->getId(), $data['hydra:member'][1]['id']);
    }

    public function testArticleWithAuthorInformation(): void
    {
        $article = $this->createTestArticle('Article with Author', ArticleStatus::PUBLISHED);
        $article->setAuthor($this->testUser);

        $this->entityManager->flush();

        $response = static::createClient()->request('GET', '/api/articles/' . $article->getId());

        $this->assertResponseIsSuccessful();

        $data = $response->toArray();
        $this->assertArrayHasKey('author', $data);
        $this->assertEquals($this->testUser->getUsername(), $data['author']['username']);
    }

    private function createTestArticle(string $title, string $status): Article
    {
        $article = new Article();
        $article->setStatus($status);
        $article->setAuthor($this->testUser);

        if ($status === ArticleStatus::PUBLISHED) {
            $article->setPublishedAt(new \DateTime());
        }

        // Créer la traduction par défaut
        $translation = new ArticleTranslation();
        $translation->setTranslatable($article);
        $translation->setLocale('en');
        $translation->setTitle($title);
        $translation->setContent('Test content for ' . $title);

        $article->addTranslation($translation);

        $this->entityManager->persist($article);
        $this->entityManager->persist($translation);

        return $article;
    }
}
