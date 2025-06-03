<?php

declare(strict_types=1);

namespace ProjetNormandie\ArticleBundle\Tests\ValueObject;

use PHPUnit\Framework\TestCase;
use ProjetNormandie\ArticleBundle\ValueObject\ArticleStatus;
use Webmozart\Assert\InvalidArgumentException;

class ArticleStatusTest extends TestCase
{
    public function testValidStatusCreation(): void
    {
        $status = new ArticleStatus(ArticleStatus::PUBLISHED);

        $this->assertEquals(ArticleStatus::PUBLISHED, $status->getValue());
        $this->assertTrue($status->isPublished());
    }

    public function testUnderConstructionStatus(): void
    {
        $status = new ArticleStatus(ArticleStatus::UNDER_CONSTRUCTION);

        $this->assertEquals(ArticleStatus::UNDER_CONSTRUCTION, $status->getValue());
        $this->assertFalse($status->isPublished());
    }

    public function testCanceledStatus(): void
    {
        $status = new ArticleStatus(ArticleStatus::CANCELED);

        $this->assertEquals(ArticleStatus::CANCELED, $status->getValue());
        $this->assertFalse($status->isPublished());
    }

    public function testInvalidStatusThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ArticleStatus('INVALID_STATUS');
    }

    public function testToString(): void
    {
        $status = new ArticleStatus(ArticleStatus::PUBLISHED);

        $this->assertEquals(ArticleStatus::PUBLISHED, (string) $status);
    }

    public function testGetStatusChoices(): void
    {
        $choices = ArticleStatus::getStatusChoices();

        $this->assertIsArray($choices);
        $this->assertArrayHasKey(ArticleStatus::UNDER_CONSTRUCTION, $choices);
        $this->assertArrayHasKey(ArticleStatus::PUBLISHED, $choices);
        $this->assertArrayHasKey(ArticleStatus::CANCELED, $choices);

        $this->assertEquals(ArticleStatus::UNDER_CONSTRUCTION, $choices[ArticleStatus::UNDER_CONSTRUCTION]);
        $this->assertEquals(ArticleStatus::PUBLISHED, $choices[ArticleStatus::PUBLISHED]);
        $this->assertEquals(ArticleStatus::CANCELED, $choices[ArticleStatus::CANCELED]);
    }

    public function testInArrayValidation(): void
    {
        // Ne devrait pas lever d'exception
        ArticleStatus::inArray(ArticleStatus::PUBLISHED);
        ArticleStatus::inArray(ArticleStatus::UNDER_CONSTRUCTION);
        ArticleStatus::inArray(ArticleStatus::CANCELED);

        $this->assertTrue(true); // Si on arrive ici, aucune exception n'a été levée
    }

    public function testInArrayValidationWithInvalidValue(): void
    {
        $this->expectException(InvalidArgumentException::class);

        ArticleStatus::inArray('INVALID_STATUS');
    }

    public function testConstantsValues(): void
    {
        $this->assertEquals('UNDER CONSTRUCTION', ArticleStatus::UNDER_CONSTRUCTION);
        $this->assertEquals('PUBLISHED', ArticleStatus::PUBLISHED);
        $this->assertEquals('CANCELED', ArticleStatus::CANCELED);
    }

    public function testValuesArray(): void
    {
        $expectedValues = [
            ArticleStatus::UNDER_CONSTRUCTION,
            ArticleStatus::PUBLISHED,
            ArticleStatus::CANCELED,
        ];

        $this->assertEquals($expectedValues, ArticleStatus::VALUES);
    }
}
