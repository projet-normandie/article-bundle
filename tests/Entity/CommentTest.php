<?php

declare(strict_types=1);

namespace ProjetNormandie\ArticleBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use ProjetNormandie\ArticleBundle\Entity\Article;
use ProjetNormandie\ArticleBundle\Entity\Comment;

#[Group('unit')]
#[Group('entity')]
class CommentTest extends TestCase
{
    private Comment $comment;
    private Article $article;

    protected function setUp(): void
    {
        $this->comment = new Comment();
        $this->article = new Article();
    }

    public function testNewCommentHasDefaultValues(): void
    {
        $this->assertNull($this->comment->getId());
        //$this->assertNotNull($this->comment->getCreatedAt());
        //$this->assertNotNull($this->comment->getUpdatedAt());
    }

    public function testSetAndGetContent(): void
    {
        $content = 'This is a test comment content';

        $this->comment->setContent($content);

        $this->assertEquals($content, $this->comment->getContent());
    }

    public function testSetAndGetArticle(): void
    {
        $this->comment->setArticle($this->article);

        $this->assertSame($this->article, $this->comment->getArticle());
    }

    public function testSetAndGetUser(): void
    {
        // Use the test-specific User entity
        $user = \ProjetNormandie\ArticleBundle\Tests\Fixtures\User::createForTest(1, 'testuser');

        $this->comment->setUser($user);

        $this->assertSame($user, $this->comment->getUser());
        $this->assertEquals(1, $this->comment->getUser()->getId());
        $this->assertEquals('testuser', $this->comment->getUser()->getUsername());
    }

    public function testToString(): void
    {
        $this->comment->setId(123);

        $expected = 'comment [123]';
        $this->assertEquals($expected, (string) $this->comment);
    }
}
