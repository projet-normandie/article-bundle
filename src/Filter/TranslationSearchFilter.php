<?php

declare(strict_types=1);

// src/Filter/TranslationSearchFilter.php
namespace ProjetNormandie\ArticleBundle\Filter;

use ApiPlatform\Doctrine\Orm\Filter\AbstractFilter;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use Doctrine\ORM\QueryBuilder;
use ProjetNormandie\ArticleBundle\Service\LocaleResolver;
use Symfony\Component\HttpFoundation\RequestStack;

final class TranslationSearchFilter extends AbstractFilter
{
    private RequestStack $requestStack;
    private LocaleResolver $localeResolver;

    public function __construct(
        RequestStack $requestStack,
        LocaleResolver $localeResolver,
    ) {
        $this->requestStack = $requestStack;
        $this->localeResolver = $localeResolver;
        parent::__construct();
    }

    protected function filterProperty(
        string $property,
        $value,
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = []
    ): void {
        // On ne traite que le paramètre 'search'
        if ($property !== 'search') {
            return;
        }

        if (empty($value)) {
            return;
        }

        $request = $this->requestStack->getCurrentRequest();
        $locale = $this->localeResolver->getPreferredLocale($request);

        $rootAlias = $queryBuilder->getRootAliases()[0];
        $alias = $queryNameGenerator->generateJoinAlias('translation');
        $parameterName = $queryNameGenerator->generateParameterName('search');

        // Recherche dans title OU content pour la locale courante OU locale par défaut
        $queryBuilder
            ->leftJoin($rootAlias . '.translations', $alias)
            ->andWhere(sprintf(
                '(%s.locale = :locale OR %s.locale = :defaultLocale) AND (%s.title LIKE :%s OR %s.content LIKE :%s)',
                $alias,
                $alias,
                $alias,
                $parameterName,
                $alias,
                $parameterName
            ))
            ->setParameter('locale', $locale)
            ->setParameter('defaultLocale', $this->localeResolver->getDefaultLocale())
            ->setParameter($parameterName, '%' . $value . '%');
    }

    public function getDescription(string $resourceClass): array
    {
        return [
            'search' => [
                'property' => 'search',
                'type' => 'string',
                'required' => false,
                'description' => 'Search in article title and content (current locale)',
                'openapi' => [
                    'example' => 'mot clé recherché',
                ],
            ],
        ];
    }
}
