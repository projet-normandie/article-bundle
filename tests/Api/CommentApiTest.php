<?php

declare(strict_types=1);

namespace ProjetNormandie\ArticleBundle\Tests\Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use Doctrine\ORM\EntityManagerInterface;
use ProjetNormandie\ArticleBundle\Entity\Article;
use ProjetNormandie\ArticleBundle\Entity\ArticleTranslation;
use ProjetNormandie\ArticleBundle\Entity\Comment;
use ProjetNormandie\ArticleBundle\Tests\Fixtures\User;
use ProjetNormandie\ArticleBundle\ValueObject\ArticleStatus;
use PHPUnit\Framework\Attributes\Group;
use ProjetNormandie\ArticleBundle\Tests\Helper\ApiTestTrait;

#[Group('api')]
#[Group('integration')]
class CommentApiTest extends ApiTestCase
{
    use ApiTestTrait;

    private EntityManagerInterface $entityManager;
    private User $testUser;
    private User $anotherUser;
    private Article $testArticle;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure that the schema exists
        $this->ensureSchema();

        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);

        // Create and persist real test users
        $this->testUser = $this->createAndPersistTestUser(1, 'testuser');
        $this->anotherUser = $this->createAndPersistTestUser(2, 'anotheruser');

        // Create a test article
        $this->testArticle = $this->createTestArticle('Test Article', ArticleStatus::PUBLISHED, $this->testUser);

        $this->entityManager->flush();
    }

    protected function tearDown(): void
    {
        $this->cleanDatabase();
        parent::tearDown();
    }

    public function testGetCommentCollection(): void
    {
        $comment1 = $this->createTestComment('First comment');
        $comment2 = $this->createTestComment('Second comment');

        $this->entityManager->flush();

        $response = static::createClient()->request('GET', '/api/article_comments');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $data = $response->toArray();
        $this->assertArrayHasKey('hydra:member', $data);
        $this->assertCount(2, $data['hydra:member']);

        // Check the data structure
        $firstComment = $data['hydra:member'][0];
        $this->assertArrayHasKey('id', $firstComment);
        $this->assertArrayHasKey('content', $firstComment);
        $this->assertArrayHasKey('createdAt', $firstComment);
        $this->assertArrayHasKey('user', $firstComment);
    }

    public function testGetSingleComment(): void
    {
        $comment = $this->createTestComment('Test comment content');
        $this->entityManager->flush();

        $response = static::createClient()->request('GET', '/api/article_comments/' . $comment->getId());

        $this->assertResponseIsSuccessful();

        $data = $response->toArray();
        $this->assertEquals($comment->getId(), $data['id']);
        $this->assertEquals('Test comment content', $data['content']);
        $this->assertArrayHasKey('user', $data);
        $this->assertEquals($this->testUser->getUsername(), $data['user']['username']);
    }

    public function testGetCommentsForSpecificArticle(): void
    {
        // Create comments for the test article
        $comment1 = $this->createTestComment('Comment 1');
        $comment2 = $this->createTestComment('Comment 2');

        // Create another article with a comment
        $anotherArticle = $this->createTestArticle('Another Article');
        $anotherComment = new Comment();
        $anotherComment->setContent('Comment for another article');
        $anotherComment->setArticle($anotherArticle);
        $anotherComment->setUser($this->testUser);

        $this->entityManager->persist($anotherComment);
        $this->entityManager->flush();

        // Test the article-specific endpoint
        $response = static::createClient()->request(
            'GET',
            '/api/articles/' . $this->testArticle->getId() . '/comments'
        );

        $this->assertResponseIsSuccessful();

        $data = $response->toArray();
        $this->assertCount(2, $data['hydra:member']);

        // Check that all comments belong to the correct article
        foreach ($data['hydra:member'] as $commentData) {
            // Note: In the response, we don't have the complete article,
            // but we know that all come from the correct article by the URL
            $this->assertArrayHasKey('content', $commentData);
        }
    }

    public function testCreateComment(): void
    {
        $client = static::createClient();

        // Authenticate the user
        $client->loginUser($this->testUser);

        $response = $client->request('POST', '/api/article_comments', [
            'json' => [
                'content' => 'New comment content',
                'article' => '/api/articles/' . $this->testArticle->getId()
            ]
        ]);

        $this->assertResponseStatusCodeSame(201);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        $data = $response->toArray();
        $this->assertEquals('New comment content', $data['content']);
        $this->assertEquals($this->testUser->getId(), $data['user']['id']);

        // Check that the comment has been saved to the database
        $savedComment = $this->entityManager->getRepository(Comment::class)->find($data['id']);
        $this->assertNotNull($savedComment);
        $this->assertEquals('New comment content', $savedComment->getContent());

        // Check that the article's comment counter has been updated
        $this->entityManager->refresh($this->testArticle);
        $this->assertEquals(1, $this->testArticle->getNbComment());
    }

    public function testCreateCommentRequiresAuthentication(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/article_comments', [
            'json' => [
                'content' => 'Unauthorized comment',
                'article' => '/api/articles/' . $this->testArticle->getId()
            ]
        ]);

        $this->assertResponseStatusCodeSame(401);
    }

    public function testUpdateOwnComment(): void
    {
        $comment = $this->createTestComment('Original content');
        $this->entityManager->flush();

        $client = static::createClient();
        $client->loginUser($this->testUser);

        $response = $client->request('PUT', '/api/article_comments/' . $comment->getId(), [
            'json' => [
                'content' => 'Updated content'
            ]
        ]);

        $this->assertResponseIsSuccessful();

        $data = $response->toArray();
        $this->assertEquals('Updated content', $data['content']);

        // Check in the database
        $this->entityManager->refresh($comment);
        $this->assertEquals('Updated content', $comment->getContent());
    }

    public function testCannotUpdateOtherUserComment(): void
    {
        $comment = $this->createTestComment('Original content');
        $this->entityManager->flush();

        $client = static::createClient();
        $client->loginUser($this->anotherUser); // Use another user

        $client->request('PUT', '/api/article_comments/' . $comment->getId(), [
            'json' => [
                'content' => 'Trying to update someone else comment'
            ]
        ]);

        $this->assertResponseStatusCodeSame(403);
    }

    public function testUpdateCommentRequiresAuthentication(): void
    {
        $comment = $this->createTestComment('Original content');
        $this->entityManager->flush();

        $client = static::createClient();

        $client->request('PUT', '/api/article_comments/' . $comment->getId(), [
            'json' => [
                'content' => 'Unauthorized update'
            ]
        ]);

        $this->assertResponseStatusCodeSame(401);
    }

    public function testCommentOrderingById(): void
    {
        $comment1 = $this->createTestComment('First comment');
        $comment2 = $this->createTestComment('Second comment');

        $this->entityManager->flush();

        $response = static::createClient()->request('GET', '/api/article_comments');

        $this->assertResponseIsSuccessful();

        $data = $response->toArray();

        // Les commentaires doivent être triés par ID ASC
        $this->assertTrue($data['hydra:member'][0]['id'] < $data['hydra:member'][1]['id']);
    }

    public function testGetNonExistentComment(): void
    {
        static::createClient()->request('GET', '/api/article_comments/99999');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testCommentValidation(): void
    {
        $client = static::createClient();
        $client->loginUser($this->testUser);

        // Test with empty content
        $response = $client->request('POST', '/api/article_comments', [
            'json' => [
                'content' => '',
                'article' => '/api/articles/' . $this->testArticle->getId()
            ]
        ]);

        $this->assertResponseStatusCodeSame(422);

        // Test without article
        $response = $client->request('POST', '/api/article_comments', [
            'json' => [
                'content' => 'Comment without article'
            ]
        ]);

        $this->assertResponseStatusCodeSame(400);
    }

    private function createTestComment(string $content): Comment
    {
        $comment = new Comment();
        $comment->setContent($content);
        $comment->setArticle($this->testArticle);
        $comment->setUser($this->testUser);

        $this->entityManager->persist($comment);

        return $comment;
    }

    private function createTestArticle(string $title = 'Test Article'): Article
    {
        $article = new Article();
        $article->setStatus(ArticleStatus::PUBLISHED);
        $article->setAuthor($this->testUser);
        $article->setPublishedAt(new \DateTime());

        // Create the default translation
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
