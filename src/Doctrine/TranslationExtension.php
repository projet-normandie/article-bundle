<?php

declare(strict_types=1);

namespace ProjetNormandie\ArticleBundle\Doctrine;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use Doctrine\ORM\QueryBuilder;
use ProjetNormandie\ArticleBundle\Entity\Article;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Doctrine ORM extension that automatically joins and preloads Article translations.
 * This extension optimizes database queries by eagerly loading translation data
 * and preparing both requested and fallback locales for efficient retrieval.
 */
final class TranslationExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{
    private RequestStack $requestStack;
    private string $defaultLocale;

    public function __construct(RequestStack $requestStack, string $defaultLocale = 'en')
    {
        $this->requestStack = $requestStack;
        $this->defaultLocale = $defaultLocale;
    }

    /**
     * Applies translation joins to collection queries.
     * Ensures all Article collections have their translations preloaded.
     */
    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = []
    ): void {
        $this->addWhere($queryBuilder, $resourceClass);
    }

    /**
     * Applies translation joins to single item queries.
     * Ensures individual Article entities have their translations preloaded.
     */
    public function applyToItem(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        array $identifiers,
        ?Operation $operation = null,
        array $context = []
    ): void {
        $this->addWhere($queryBuilder, $resourceClass);
    }

    /**
     * Adds translation joins and selects to the query builder.
     * Optimizes database access by preloading translation data and
     * specifically preparing requested and default locale translations.
     */
    private function addWhere(QueryBuilder $queryBuilder, string $resourceClass): void
    {
        if ($resourceClass !== Article::class) {
            return;
        }

        $request = $this->requestStack->getCurrentRequest();
        $requestedLocale = $request ? $request->getLocale() : $this->defaultLocale;

        $rootAlias = $queryBuilder->getRootAliases()[0];

        // Join all available translations
        $queryBuilder->leftJoin($rootAlias . '.translations', 't')
            ->addSelect('t');

        // Optional: you can also preload specifically the requested translation
        // and default translation for optimal performance
        $queryBuilder
            ->leftJoin(
                $rootAlias . '.translations',
                't_requested',
                'WITH',
                't_requested.locale = :requestedLocale'
            )
            ->leftJoin(
                $rootAlias . '.translations',
                't_default',
                'WITH',
                't_default.locale = :defaultLocale'
            )
            ->addSelect('t_requested')
            ->addSelect('t_default')
            ->setParameter('requestedLocale', $requestedLocale)
            ->setParameter('defaultLocale', $this->defaultLocale);
    }
}
