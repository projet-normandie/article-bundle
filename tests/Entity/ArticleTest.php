<?php

declare(strict_types=1);

namespace ProjetNormandie\ArticleBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use ProjetNormandie\ArticleBundle\Entity\Article;
use ProjetNormandie\ArticleBundle\Entity\ArticleTranslation;
use ProjetNormandie\ArticleBundle\ValueObject\ArticleStatus;

#[Group('unit')]
#[Group('entity')]
class ArticleTest extends TestCase
{
    private Article $article;

    protected function setUp(): void
    {
        $this->article = new Article();
    }

    public function testNewArticleHasDefaultValues(): void
    {
        $this->assertNull($this->article->getId());
        $this->assertEquals(ArticleStatus::UNDER_CONSTRUCTION, $this->article->getStatus());
        $this->assertEquals(0, $this->article->getNbComment());
        $this->assertNull($this->article->getPublishedAt());
        $this->assertEmpty($this->article->getComments());
        $this->assertEmpty($this->article->getTranslations());
    }

    public function testSetAndGetBasicProperties(): void
    {
        $publishedAt = new \DateTime();

        $this->article->setStatus(ArticleStatus::PUBLISHED);
        $this->article->setNbComment(5);
        $this->article->setPublishedAt($publishedAt);
        $this->article->setSlug('test-slug');

        $this->assertEquals(ArticleStatus::PUBLISHED, $this->article->getStatus());
        $this->assertEquals(5, $this->article->getNbComment());
        $this->assertEquals($publishedAt, $this->article->getPublishedAt());
        $this->assertEquals('test-slug', $this->article->getSlug());
    }

    public function testArticleStatusValueObject(): void
    {
        $this->article->setStatus(ArticleStatus::PUBLISHED);

        $articleStatus = $this->article->getArticleStatus();
        $this->assertInstanceOf(ArticleStatus::class, $articleStatus);
        $this->assertTrue($articleStatus->isPublished());
        $this->assertEquals(ArticleStatus::PUBLISHED, $articleStatus->getValue());
    }

    public function testTranslationManagement(): void
    {
        // Add english translation
        $enTranslation = new ArticleTranslation();
        $enTranslation->setLocale('en');
        $enTranslation->setTitle('English Title');
        $enTranslation->setContent('English Content');

        $this->article->addTranslation($enTranslation);

        $this->assertCount(1, $this->article->getTranslations());
        $this->assertTrue($this->article->hasTranslation('en'));
        $this->assertFalse($this->article->hasTranslation('fr'));
        $this->assertEquals(['en'], $this->article->getAvailableLocales());

        // Add french translation
        $frTranslation = new ArticleTranslation();
        $frTranslation->setLocale('fr');
        $frTranslation->setTitle('Titre Français');
        $frTranslation->setContent('Contenu Français');

        $this->article->addTranslation($frTranslation);

        $this->assertCount(2, $this->article->getTranslations());
        $this->assertTrue($this->article->hasTranslation('fr'));
        $this->assertContains('fr', $this->article->getAvailableLocales());
    }

    public function testSetAndGetTitleWithLocale(): void
    {
        $this->article->setTitle('English Title', 'en');
        $this->article->setTitle('Titre Français', 'fr');

        $this->assertEquals('English Title', $this->article->getTitle('en'));
        $this->assertEquals('Titre Français', $this->article->getTitle('fr'));

        // Test fallback au titre par défaut
        $this->assertEquals('English Title', $this->article->getDefaultTitle());
    }

    public function testSetAndGetContentWithLocale(): void
    {
        $this->article->setContent('English Content', 'en');
        $this->article->setContent('Contenu Français', 'fr');

        $this->assertEquals('English Content', $this->article->getContent('en'));
        $this->assertEquals('Contenu Français', $this->article->getContent('fr'));

        // Test fallback to default content
        $this->assertEquals('English Content', $this->article->getDefaultContent());
    }

    public function testCurrentLocaleHandling(): void
    {
        $this->article->setTitle('English Title', 'en');
        $this->article->setTitle('Titre Français', 'fr');

        // Without current locale, uses the default locale
        $this->assertEquals('English Title', $this->article->getTitle());

        // With current locale set
        $this->article->setCurrentLocale('fr');
        $this->assertEquals('fr', $this->article->getCurrentLocale());
        $this->assertEquals('Titre Français', $this->article->getTitle());
    }

    public function testTranslateFallbackLogic(): void
    {
        // Create only a French translation
        $frTranslation = new ArticleTranslation();
        $frTranslation->setLocale('fr');
        $frTranslation->setTitle('Titre Français');
        $frTranslation->setContent('Contenu Français');
        $this->article->addTranslation($frTranslation);

        // Request the English translation (doesn't exist)
        $translation = $this->article->translate('en');

        // Should return the French translation (only available)
        $this->assertEquals('fr', $translation->getLocale());
        $this->assertEquals('Titre Français', $translation->getTitle());
    }

    public function testToString(): void
    {
        $this->article->setId(123);
        $this->article->setTitle('Test Title', 'en');

        $expected = 'Test Title [123]';
        $this->assertEquals($expected, (string) $this->article);
    }

    public function testRemoveTranslation(): void
    {
        $translation = new ArticleTranslation();
        $translation->setLocale('en');
        $translation->setTitle('English Title');

        $this->article->addTranslation($translation);
        $this->assertCount(1, $this->article->getTranslations());

        $this->article->removeTranslation($translation);
        $this->assertCount(0, $this->article->getTranslations());
    }
}
