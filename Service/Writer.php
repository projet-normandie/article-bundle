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
        foreach ($title as $lang => $value) {
            $article->translate($lang, false)->setTitle($value);
        }
        foreach ($text as $lang => $value) {
            $article->translate($lang, false)->setText($value);
        }
        $article->setAuthor($author);
        $article->setStatus(Article::STATUS_PUBLISHED);
        $article->setPublishedAt(new \Datetime());
        $article->mergeNewTranslations();

        $this->em->persist($article);
        $this->em->flush();
    }
}
