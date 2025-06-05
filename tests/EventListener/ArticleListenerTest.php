<?php

declare(strict_types=1);

namespace ProjetNormandie\ArticleBundle\Tests\EventListener;

use PHPUnit\Framework\TestCase;
use ProjetNormandie\ArticleBundle\Entity\Article;
use ProjetNormandie\ArticleBundle\Tests\Fixtures\User;
use ProjetNormandie\ArticleBundle\EventListener\Entity\ArticleListener;
use ProjetNormandie\ArticleBundle\Enum\ArticleStatus;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\String\Slugger\SluggerInterface;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use ProjetNormandie\ArticleBundle\Tests\Helper\TestUserTrait;
use Symfony\Component\String\UnicodeString;

class ArticleListenerTest extends TestCase
{
    use TestUserTrait;
    private ArticleListener $listener;
    private Security $security;
    private SluggerInterface $slugger;
    private User $user;

    protected function setUp(): void
    {
        $this->security = $this->createMock(Security::class);
        $this->slugger = $this->createMock(SluggerInterface::class);

        // Create a test user with the trait
        $this->user = $this->createTestUser(1, 'testuser');

        $this->listener = new ArticleListener($this->security, $this->slugger);
    }

    public function testPrePersistSetsAuthorWhenNull(): void
    {
        $article = new Article();
        $article->setTitle('Test Article', 'en');
        $article->setStatus(ArticleStatus::UNDER_CONSTRUCTION);

        $this->security->expects($this->once())
            ->method('getUser')
            ->willReturn($this->user);

        $this->slugger->expects($this->once())
            ->method('slug')
            ->with('Test Article')
            ->willReturn(new UnicodeString('test-article'));

        $this->listener->prePersist($article);

        $this->assertSame($this->user, $article->getAuthor());
        $this->assertEquals('test-article', $article->getSlug());
    }

    public function testPrePersistDoesNotOverrideExistingAuthor(): void
    {
        // Créer un autre utilisateur existant
        $existingAuthor = $this->createTestUser(999, 'existing');

        $article = new Article();
        $article->setAuthor($existingAuthor);
        $article->setTitle('Test Article', 'en');
        $article->setStatus(ArticleStatus::UNDER_CONSTRUCTION);

        $this->security->expects($this->never())
            ->method('getUser');

        $this->slugger->expects($this->once())
            ->method('slug')
            ->with('Test Article')
            ->willReturn(new UnicodeString('test-article'));

        $this->listener->prePersist($article);

        $this->assertSame($existingAuthor, $article->getAuthor());
    }

    public function testPrePersistSetsPublishedAtForPublishedArticle(): void
    {
        $article = new Article();
        $article->setTitle('Test Article', 'en');
        $article->setStatus(ArticleStatus::PUBLISHED);

        $this->security->expects($this->once())
            ->method('getUser')
            ->willReturn($this->user);

        $this->slugger->expects($this->once())
            ->method('slug')
            ->willReturn(new UnicodeString('test-article'));

        $this->listener->prePersist($article);

        $this->assertInstanceOf(\DateTime::class, $article->getPublishedAt());
    }

    public function testPreUpdateSetsPublishedAtWhenChangingToPublished(): void
    {
        $article = new Article();
        $article->setTitle('Test Article', 'en');
        $article->setStatus(ArticleStatus::PUBLISHED);

        $changeSet = ['status' => [ArticleStatus::UNDER_CONSTRUCTION, ArticleStatus::PUBLISHED]];
        $event = $this->createMock(PreUpdateEventArgs::class);
        $event->method('getEntityChangeSet')->willReturn($changeSet);

        $this->slugger->expects($this->once())
            ->method('slug')
            ->with('Test Article')
            ->willReturn(new UnicodeString('test-article'));

        $this->listener->preUpdate($article, $event);

        $this->assertInstanceOf(\DateTime::class, $article->getPublishedAt());
    }
}
