<?php

declare(strict_types=1);

namespace ProjetNormandie\ArticleBundle\EventListener\Entity;

use Datetime;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use ProjetNormandie\ArticleBundle\Entity\ArticleTranslation;

class ArticleTranslationListener
{
    public function postUpdate(ArticleTranslation $translation, PostUpdateEventArgs $event): void
    {
        $article = $translation->getTranslatable();

        if ($article !== null) {
            $em = $event->getObjectManager();

            $article->setUpdatedAt(new DateTime());

            $em->persist($article);
            $em->flush();
        }
    }
}
