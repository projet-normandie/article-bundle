<?php

namespace ProjetNormandie\ArticleBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use ProjetNormandie\ArticleBundle\Entity\Article;
use ProjetNormandie\ArticleBundle\Entity\ArticleTranslation;

#[Group('unit')]
#[Group('entity')]
class ArticleTranslationTest extends TestCase
{
    private ArticleTranslation $translation;
    private Article $article;

    protected function setUp(): void
    {
        $this->translation = new ArticleTranslation();
        $this->article = new Article();
    }

    public function testNewTranslationHasDefaultValues(): void
    {
        $this->assertNull($this->translation->getId());
        $this->assertEquals('', $this->translation->getTitle());
        $this->assertEquals('', $this->translation->getContent());
    }

    public function testSetAndGetBasicProperties(): void
    {
        $title = 'Test Title';
        $content = 'Test Content';
        $locale = 'fr';

        $this->translation->setTitle($title);
        $this->translation->setContent($content);
        $this->translation->setLocale($locale);
        $this->translation->setTranslatable($this->article);

        $this->assertEquals($title, $this->translation->getTitle());
        $this->assertEquals($content, $this->translation->getContent());
        $this->assertEquals($locale, $this->translation->getLocale());
        $this->assertSame($this->article, $this->translation->getTranslatable());
    }

    public function testToString(): void
    {
        $title = 'Test Title';
        $this->translation->setTitle($title);

        $this->assertEquals($title, (string) $this->translation);

        // Test with empty title
        $emptyTranslation = new ArticleTranslation();
        $this->assertEquals('', (string) $emptyTranslation);
    }
}
