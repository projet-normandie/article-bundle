<?php

namespace ProjetNormandie\ArticleBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use ProjetNormandie\ArticleBundle\Entity\Article;

/**
 * Proxy to write an article
 */
class Writer
{
    private EntityManagerInterface $em;
    private string $pnDefaultUserId;

    /**
     * @param EntityManagerInterface $em
     * @param string                 $pnDefaultUserId
     */
    public function __construct(EntityManagerInterface $em, string $pnDefaultUserId)
    {
        $this->em = $em;
        $this->pnDefaultUserId = $pnDefaultUserId;
    }

    /**
     * @param $title
     * @param $text
     * @param $author
     * @return void
     * @throws ORMException
     */
    public function write($title, $text, $author = null)
    {
        $article = new Article();
        foreach ($title as $lang => $value) {
            $article->translate($lang, false)->setTitle($value);
        }
        foreach ($text as $lang => $value) {
            $article->translate($lang, false)->setText($value);
        }
        if ($author != null) {
            $article->setAuthor($author);
        } else {
            $article->setAuthor($this->em->getReference('ProjetNormandie\ArticleBundle\Entity\UserInterface', $this->pnDefaultUserId));
        }
        $article->setStatus(Article::STATUS_PUBLISHED);
        $article->setPublishedAt(new \Datetime());
        $article->mergeNewTranslations();

        $this->em->persist($article);
        $this->em->flush();
    }
}
