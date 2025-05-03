<?php

declare(strict_types=1);

namespace ProjetNormandie\ArticleBundle\EventListener\Entity;

use Datetime;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use ProjetNormandie\ArticleBundle\Entity\ArticleTranslation;

readonly class ArticleTranslationListener
{
    public function preUpdate(ArticleTranslation $translation, PreUpdateEventArgs $event): void
    {
        $translation->getTranslatable()->setUpdatedAt(new Datetime());
    }
}
