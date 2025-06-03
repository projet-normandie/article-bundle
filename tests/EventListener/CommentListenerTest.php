<?php

declare(strict_types=1);

namespace ProjetNormandie\ArticleBundle\Tests\EventListener;

use PHPUnit\Framework\TestCase;
use ProjetNormandie\ArticleBundle\Entity\Article;
use ProjetNormandie\ArticleBundle\Entity\Comment;
use ProjetNormandie\ArticleBundle\Tests\Fixtures\User;
use ProjetNormandie\ArticleBundle\EventListener\Entity\CommentListener;
use Symfony\Bundle\SecurityBundle\Security;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use ProjetNormandie\ArticleBundle\Tests\Helper\TestUserTrait;

class CommentListenerTest extends TestCase
{
    use TestUserTrait;
    private CommentListener $listener;
    private Security $security;
    private User $user;

    protected function setUp(): void
    {
        $this->security = $this->createMock(Security::class);

        // Create a test user with the trait
        $this->user = $this->createTestUser(1, 'testuser');

        $this->listener = new CommentListener($this->security);
    }

    public function testPrePersistSetsUserAndIncrementsArticleCommentCount(): void
    {
        $article = new Article();
        $article->setNbComment(5);

        $comment = new Comment();
        $comment->setArticle($article);
        $comment->setContent('Test comment');

        $event = $this->createMock(LifecycleEventArgs::class);

        $this->security->expects($this->once())
            ->method('getUser')
            ->willReturn($this->user);

        $this->listener->prePersist($comment, $event);

        $this->assertSame($this->user, $comment->getUser());
        $this->assertEquals(6, $article->getNbComment());
    }

    public function testPreRemoveDecrementsArticleCommentCount(): void
    {
        $article = new Article();
        $article->setNbComment(5);

        $comment = new Comment();
        $comment->setArticle($article);
        $comment->setContent('Test comment');

        $event = $this->createMock(LifecycleEventArgs::class);

        $this->listener->preRemove($comment, $event);

        $this->assertEquals(4, $article->getNbComment());
    }

    public function testPreRemoveHandlesZeroCommentCount(): void
    {
        $article = new Article();
        $article->setNbComment(0);

        $comment = new Comment();
        $comment->setArticle($article);
        $comment->setContent('Test comment');

        $event = $this->createMock(LifecycleEventArgs::class);

        $this->listener->preRemove($comment, $event);

        $this->assertEquals(-1, $article->getNbComment());
    }
}