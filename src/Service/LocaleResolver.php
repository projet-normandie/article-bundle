<?php

declare(strict_types=1);

// src/Service/LocaleResolver.php
namespace ProjetNormandie\ArticleBundle\Service;

use Symfony\Component\HttpFoundation\Request;

class LocaleResolver
{
    private array $supportedLocales;
    private string $defaultLocale;

    public function __construct(
        array $supportedLocales = ['en', 'fr'],
        string $defaultLocale = 'en'
    ) {
        $this->supportedLocales = $supportedLocales;
        $this->defaultLocale = $defaultLocale;
    }

    /**
     * Extract preferred locale from the HTTP_ACCEPT_LANGUAGE header
     */
    public function getPreferredLocale(Request $request): string
    {
        // Check for HTTP_ACCEPT_LANGUAGE header
        $acceptLanguage = $request->headers->get('Accept-Language');
        if (!$acceptLanguage) {
            return $this->defaultLocale;
        }

        // Extract primary language code (fr-FR -> fr, en-US -> en, etc.)
        $locale = substr($acceptLanguage, 0, 2);

        // Only accept supported locales
        if (in_array($locale, $this->supportedLocales, true)) {
            return $locale;
        }
        return $this->defaultLocale;
    }

    public function getSupportedLocales(): array
    {
        return $this->supportedLocales;
    }

    public function getDefaultLocale(): string
    {
        return $this->defaultLocale;
    }
}
