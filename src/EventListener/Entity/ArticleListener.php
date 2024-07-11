<?php

declare(strict_types=1);

namespace ProjetNormandie\ArticleBundle\EventListener\Entity;

use Doctrine\ORM\Event\PreUpdateEventArgs;
use ProjetNormandie\ArticleBundle\Entity\Article;
use Symfony\Bundle\SecurityBundle\Security;

class ArticleListener
{
    public function __construct(private readonly Security $security)
    {
    }

    public function prePersist(Article $article): void
    {
        $article->setAuthor($this->security->getUser());
        if ($article->getArticleStatus()->isPublished()) {
            $article->setPublishedAt(new \DateTime());
        }
    }

    public function preUpdate(Article $article, PreUpdateEventArgs $event): void
    {
        if ($article->getArticleStatus()->isPublished() && $article->getPublishedAt() === null) {
            $article->setPublishedAt(new \DateTime());
        }
    }
}
