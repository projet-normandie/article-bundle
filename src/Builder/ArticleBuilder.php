<?php

declare(strict_types=1);

namespace ProjetNormandie\ArticleBundle\Builder;

use Doctrine\ORM\EntityManagerInterface;
use ProjetNormandie\ArticleBundle\Entity\Article;
use ProjetNormandie\ArticleBundle\ValueObject\ArticleStatus;

class ArticleBuilder
{
    private $author;

    /**
     * @var string[]
     */
    private array $titles = [];

    /**
     * @var string[]
     */
    private array $texts = [];

    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function setAuthor($author): ArticleBuilder
    {
        $this->author = $author;
        return $this;
    }

    public function setTitle(string $title, string $lang): ArticleBuilder
    {
        $this->titles[$lang] = $title;
        return $this;
    }

    public function setText(string $text, string $lang): ArticleBuilder
    {
        $this->texts[$lang] = $text;
        return $this;
    }

    public function send(): void
    {
        $article = new Article();
        $article->setAuthor($this->author);
        $article->setStatus(ArticleStatus::PUBLISHED);
        $article->setPublishedAt(new \DateTime());

        foreach ($this->titles as $lang => $value) {
            $article->setTitle($value, $lang);
        }
        foreach ($this->texts as $lang => $value) {
            $article->setText($value, $lang);
        }

        $this->em->persist($article);
        $this->em->flush();
    }
}
