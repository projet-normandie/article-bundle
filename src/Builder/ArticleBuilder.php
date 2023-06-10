<?php

namespace ProjetNormandie\ArticleBundle\Builder;

use Doctrine\ORM\EntityManagerInterface;
use ProjetNormandie\ArticleBundle\Contracts\ArticleInterface;
use ProjetNormandie\ArticleBundle\Entity\Article;

class ArticleBuilder implements ArticleInterface
{
    private $author;

    private array $titles;

    private array $texts;

    private EntityManagerInterface $em;

    /**
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @param $author
     * @return ArticleBuilder
     */
    public function setAuthor($author): ArticleBuilder
    {
        $this->author = $author;
        return $this;
    }

    /**
     * @param string $title
     * @param string $lang
     * @return $this
     */
    public function setTitle(string $title, string $lang): ArticleBuilder
    {
        $this->titles[$lang] = $title;
        return $this;
    }

    /**
     * @param string $text
     * @param string $lang
     * @return $this
     */
    public function setText(string $text, string $lang): ArticleBuilder
    {
        $this->texts[$lang] = $text;
        return $this;
    }

    public function send()
    {
        $article = new Article();
        $article->setAuthor($this->author);
        $article->setStatus(self::STATUS_PUBLISHED);
        $article->setPublishedAt(new \Datetime());

        foreach ($this->titles as $lang => $value) {
            $article->translate($lang, false)->setTitle($value);
        }
        foreach ($this->texts as $lang => $value) {
            $article->translate($lang, false)->setText($value);
        }
        $article->mergeNewTranslations();

        $this->em->persist($article);
        $this->em->flush();
    }
}
