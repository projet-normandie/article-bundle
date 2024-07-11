<?php

declare(strict_types=1);

namespace ProjetNormandie\ArticleBundle\ValueObject;

use Webmozart\Assert\Assert;

class ArticleStatus
{
    public const UNDER_CONSTRUCTION = 'UNDER CONSTRUCTION';
    public const PUBLISHED = 'PUBLISHED';
    public const CANCELED = 'CANCELED';

    public const VALUES = [
        self::UNDER_CONSTRUCTION,
        self::PUBLISHED,
        self::CANCELED,
    ];

    private string $value;

    public function __construct(string $value)
    {
        self::inArray($value);

        $this->value = $value;
    }

    public static function inArray(string $value): void
    {
        Assert::inArray($value, self::VALUES);
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function isPublished(): bool
    {
        return $this->value === self::PUBLISHED;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    /**
     * @return string[]
     */
    public static function getStatusChoices(): array
    {
        return [
            self::UNDER_CONSTRUCTION => self::UNDER_CONSTRUCTION,
            self::PUBLISHED => self::PUBLISHED,
            self::CANCELED => self::CANCELED
        ];
    }
}
