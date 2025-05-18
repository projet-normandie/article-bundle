<?php

declare(strict_types=1);

namespace ProjetNormandie\ArticleBundle\EventListener;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use ProjetNormandie\ArticleBundle\Entity\Article;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Event subscriber that sets the current locale on Article entities
 * based on the HTTP_ACCEPT_LANGUAGE header
 */
class ArticleLocaleSubscriber implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            // This event is triggered right before the serialization process
            KernelEvents::VIEW => ['setLocale', EventPriorities::PRE_SERIALIZE],
        ];
    }

    /**
     * Set the appropriate locale on Article entities based on HTTP_ACCEPT_LANGUAGE
     *
     * @param ViewEvent $event The event
     */
    public function setLocale(ViewEvent $event): void
    {
        $request = $event->getRequest();
        $result = $event->getControllerResult();

        // Only process GET requests targeting Article entities
        if (!$this->shouldProcess($request, $result)) {
            return;
        }

        // Get preferred locale from HTTP_ACCEPT_LANGUAGE header
        $locale = $this->getPreferredLocale($request);

        // Set locale on one or more Article entities
        $this->applyLocaleToResult($result, $locale);
    }

    /**
     * Determine if this request should be processed
     *
     * @param Request $request The HTTP request
     * @param mixed $result The controller result
     * @return bool Whether the request should be processed
     */
    private function shouldProcess(Request $request, $result): bool
    {
        // Only process GET requests
        if (!in_array($request->getMethod(), ['GET'])) {
            return false;
        }

        // Process single Article
        if ($result instanceof Article) {
            return true;
        }

        // Process collections containing Articles
        if (is_array($result) || $result instanceof \Traversable) {
            foreach ($result as $item) {
                if ($item instanceof Article) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Extract preferred locale from the HTTP_ACCEPT_LANGUAGE header
     *
     * @param Request $request The HTTP request
     * @return string The preferred locale (defaulting to 'en')
     */
    private function getPreferredLocale(Request $request): string
    {
        // Check for HTTP_ACCEPT_LANGUAGE header
        $acceptLanguage = $request->headers->get('Accept-Language');
        if (!$acceptLanguage) {
            return 'en'; // Default to English
        }

        // Extract primary language code (fr-FR -> fr, en-US -> en, etc.)
        $locale = substr($acceptLanguage, 0, 2);

        // Only accept supported locales
        return in_array($locale, ['fr', 'en']) ? $locale : 'en';
    }

    /**
     * Apply the preferred locale to Article entities in the result
     *
     * @param mixed $result The controller result
     * @param string $locale The preferred locale
     */
    private function applyLocaleToResult($result, string $locale): void
    {
        // Single Article
        if ($result instanceof Article) {
            $result->setCurrentLocale($locale);
            return;
        }

        // Collection of Articles
        if (is_array($result) || $result instanceof \Traversable) {
            foreach ($result as $item) {
                if ($item instanceof Article) {
                    $item->setCurrentLocale($locale);
                }
            }
        }
    }
}
