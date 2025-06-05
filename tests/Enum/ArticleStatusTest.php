<?php

declare(strict_types=1);

namespace ProjetNormandie\ArticleBundle\Tests\Enum;

use PHPUnit\Framework\TestCase;
use ProjetNormandie\ArticleBundle\Enum\ArticleStatus;

class ArticleStatusTest extends TestCase
{
    public function testEnumCases(): void
    {
        $this->assertSame('UNDER CONSTRUCTION', ArticleStatus::UNDER_CONSTRUCTION->value);
        $this->assertSame('PUBLISHED', ArticleStatus::PUBLISHED->value);
        $this->assertSame('CANCELED', ArticleStatus::CANCELED->value);
    }

    public function testAllCasesArePresent(): void
    {
        $cases = ArticleStatus::cases();

        $this->assertCount(3, $cases);
        $this->assertContains(ArticleStatus::UNDER_CONSTRUCTION, $cases);
        $this->assertContains(ArticleStatus::PUBLISHED, $cases);
        $this->assertContains(ArticleStatus::CANCELED, $cases);
    }

    public function testIsPublishedMethod(): void
    {
        $this->assertTrue(ArticleStatus::PUBLISHED->isPublished());
        $this->assertFalse(ArticleStatus::UNDER_CONSTRUCTION->isPublished());
        $this->assertFalse(ArticleStatus::CANCELED->isPublished());
    }

    public function testGetStatusChoices(): void
    {
        $expected = [
            'UNDER CONSTRUCTION' => 'UNDER CONSTRUCTION',
            'PUBLISHED' => 'PUBLISHED',
            'CANCELED' => 'CANCELED',
        ];

        $this->assertSame($expected, ArticleStatus::getStatusChoices());
    }

    public function testGetValues(): void
    {
        $expected = [
            'UNDER CONSTRUCTION',
            'PUBLISHED',
            'CANCELED',
        ];

        $this->assertSame($expected, ArticleStatus::getValues());
        $this->assertCount(3, ArticleStatus::getValues());
    }

    public function testFromValue(): void
    {
        $this->assertSame(ArticleStatus::PUBLISHED, ArticleStatus::from('PUBLISHED'));
        $this->assertSame(ArticleStatus::UNDER_CONSTRUCTION, ArticleStatus::from('UNDER CONSTRUCTION'));
        $this->assertSame(ArticleStatus::CANCELED, ArticleStatus::from('CANCELED'));
    }

    public function testFromInvalidValueThrowsException(): void
    {
        $this->expectException(\ValueError::class);
        ArticleStatus::from('INVALID_STATUS');
    }

    public function testTryFromValue(): void
    {
        $this->assertSame(ArticleStatus::PUBLISHED, ArticleStatus::tryFrom('PUBLISHED'));
        $this->assertSame(ArticleStatus::UNDER_CONSTRUCTION, ArticleStatus::tryFrom('UNDER CONSTRUCTION'));
        $this->assertSame(ArticleStatus::CANCELED, ArticleStatus::tryFrom('CANCELED'));
        $this->assertNull(ArticleStatus::tryFrom('INVALID_STATUS'));
    }

    public function testEnumIsStringBacked(): void
    {
        $this->assertIsString(ArticleStatus::PUBLISHED->value);
        $this->assertIsString(ArticleStatus::UNDER_CONSTRUCTION->value);
        $this->assertIsString(ArticleStatus::CANCELED->value);
    }

    public function testEnumEquality(): void
    {
        $status1 = ArticleStatus::PUBLISHED;
        $status2 = ArticleStatus::PUBLISHED;
        $status3 = ArticleStatus::UNDER_CONSTRUCTION;

        $this->assertSame($status1, $status2);
        $this->assertNotSame($status1, $status3);
        $this->assertTrue($status1 === $status2);
        $this->assertFalse($status1 === $status3);
    }

    public function testEnumInArray(): void
    {
        $publishedStatuses = [ArticleStatus::PUBLISHED];
        $draftStatuses = [ArticleStatus::UNDER_CONSTRUCTION, ArticleStatus::CANCELED];

        $this->assertContains(ArticleStatus::PUBLISHED, $publishedStatuses);
        $this->assertNotContains(ArticleStatus::PUBLISHED, $draftStatuses);
        $this->assertContains(ArticleStatus::UNDER_CONSTRUCTION, $draftStatuses);
    }

    /**
     * Test useful for serialization/deserialization (API Platform, Symfony Forms)
     */
    public function testSerialization(): void
    {
        $status = ArticleStatus::PUBLISHED;

        // Test with json_encode (for API Platform)
        $json = json_encode($status);
        $this->assertSame('"PUBLISHED"', $json);

        // Test with var_export (for Symfony cache)
        $exported = var_export($status, true);
        $this->assertStringContainsString('PUBLISHED', $exported);
    }

    /**
     * Performance test to ensure comparisons are fast
     */
    public function testPerformance(): void
    {
        $status = ArticleStatus::PUBLISHED;

        $start = microtime(true);
        for ($i = 0; $i < 1000; $i++) {
            $isPublished = $status->isPublished();
        }
        $end = microtime(true);

        // Should be very fast (less than 1ms for 1000 calls)
        $this->assertLessThan(0.001, $end - $start);
    }

    /**
     * Business logic specific test
     */
    public function testBusinessLogic(): void
    {
        // A published article should be considered as published
        $publishedArticle = ArticleStatus::PUBLISHED;
        $this->assertTrue($publishedArticle->isPublished());

        // An article under construction or canceled should not be published
        $draftArticle = ArticleStatus::UNDER_CONSTRUCTION;
        $canceledArticle = ArticleStatus::CANCELED;

        $this->assertFalse($draftArticle->isPublished());
        $this->assertFalse($canceledArticle->isPublished());
    }

    /**
     * Test Switch/Match use cases (PHP 8.0+)
     */
    public function testSwitchStatement(): void
    {
        $getStatusLabel = function (ArticleStatus $status): string {
            return match ($status) {
                ArticleStatus::PUBLISHED => 'Published',
                ArticleStatus::UNDER_CONSTRUCTION => 'Under Construction',
                ArticleStatus::CANCELED => 'Canceled',
            };
        };

        $this->assertSame('Published', $getStatusLabel(ArticleStatus::PUBLISHED));
        $this->assertSame('Under Construction', $getStatusLabel(ArticleStatus::UNDER_CONSTRUCTION));
        $this->assertSame('Canceled', $getStatusLabel(ArticleStatus::CANCELED));
    }
}
