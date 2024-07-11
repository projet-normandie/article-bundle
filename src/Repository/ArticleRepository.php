<?php

declare(strict_types=1);

namespace ProjetNormandie\ArticleBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use ProjetNormandie\ArticleBundle\Entity\Article;

class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }
}
