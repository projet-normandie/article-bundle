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

final class TranslationExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{
    private RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = []
    ): void {
        $this->addWhere($queryBuilder, $resourceClass);
    }

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

    private function addWhere(QueryBuilder $queryBuilder, string $resourceClass): void
    {
        if ($resourceClass != Article::class) {
            return;
        }

        // Joindre les traductions
        $rootAlias = $queryBuilder->getRootAliases()[0];
        $queryBuilder->leftJoin($rootAlias . '.translations', 't')
            ->addSelect('t');

        // Si vous voulez filtrer par locale, vous pouvez le faire ici
        $request = $this->requestStack->getCurrentRequest();
        if ($request && $locale = $request->getLocale()) {
            // Optionnel : filtrer par locale courante
            // $queryBuilder->andWhere('t.locale = :locale')
            //     ->setParameter('locale', $locale);
        }
    }
}
