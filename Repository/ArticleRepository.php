<?php

namespace ProjetNormandie\ArticleBundle\Repository;

use Doctrine\ORM\EntityRepository;
use ProjetNormandie\ArticleBundle\Entity\Article;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\OptimisticLockException;

class ArticleRepository extends EntityRepository
{
}
