<?php

namespace ProjetNormandie\ArticleBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use ProjetNormandie\ArticleBundle\Entity\Article;

/**
 * Proxy to write an article
 */
class Writer
{
    private $em;

    /**
     * Message constructor.
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @param $title
     * @param $text
     * @param $author
     */
    public function write($title, $text, $author)
    {
        $article = new Article();
        $article->translate('en', false)->setTitle($title['en']);
        $article->translate('fr', false)->setTitle($title['fr']);
        $article->translate('en', false)->setText($text['en']);
        $article->translate('fr', false)->setText($text['fr']);

        $article->setAuthor($author);
        $article->mergeNewTranslations();

        $this->em->persist($article);
        $this->em->flush();
    }
}
