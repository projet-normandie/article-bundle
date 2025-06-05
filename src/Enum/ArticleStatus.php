<?php

declare(strict_types=1);

namespace ProjetNormandie\ArticleBundle\Enum;

enum ArticleStatus: string
{
    case UNDER_CONSTRUCTION = 'UNDER CONSTRUCTION';
    case PUBLISHED = 'PUBLISHED';
    case CANCELED = 'CANCELED';

    public function isPublished(): bool
    {
        return $this === self::PUBLISHED;
    }

    /**
     * @return array<string, string>
     */
    public static function getStatusChoices(): array
    {
        return [
            self::UNDER_CONSTRUCTION->value => self::UNDER_CONSTRUCTION->value,
            self::PUBLISHED->value => self::PUBLISHED->value,
            self::CANCELED->value => self::CANCELED->value,
        ];
    }

    /**
     * @return string[]
     */
    public static function getValues(): array
    {
        return array_map(fn(self $case) => $case->value, self::cases());
    }
}
