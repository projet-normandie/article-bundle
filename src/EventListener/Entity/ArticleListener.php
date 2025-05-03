<?php

declare(strict_types=1);

namespace ProjetNormandie\ArticleBundle\EventListener\Entity;

use Doctrine\ORM\Event\PreUpdateEventArgs;
use ProjetNormandie\ArticleBundle\Entity\Article;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\String\Slugger\SluggerInterface;

readonly class ArticleListener
{
    public function __construct(
        private Security $security,
        private SluggerInterface $slugger
    ) {
    }

    public function prePersist(Article $article): void
    {
        if (null === $article->getAuthor()) {
            $article->setAuthor($this->security->getUser());
        }
        if ($article->getArticleStatus()->isPublished()) {
            $article->setPublishedAt(new \DateTime());
        }

        $this->updateSlug($article);
    }

    public function preUpdate(Article $article, PreUpdateEventArgs $event): void
    {
        if ($article->getArticleStatus()->isPublished() && $article->getPublishedAt() === null) {
            $article->setPublishedAt(new \DateTime());
        }

        $this->updateSlug($article);
    }

    private function updateSlug(Article $article): void
    {
        $article->setSlug($this->slugger->slug($article->getDefaultTitle())->lower()->toString());
    }
}
