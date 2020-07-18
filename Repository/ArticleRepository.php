<?php

namespace ProjetNormandie\ArticleBundle\Repository;

use Doctrine\ORM\EntityRepository;
use ProjetNormandie\ArticleBundle\Entity\Article;

class ArticleRepository extends EntityRepository
{
    /**
     * @param $data
     * @throws \Doctrine\ORM\ORMException
     */
    public function create($data)
    {
        $article = new Article();
        $article->translate('en', false)->setTitle($data['title']['en']);
        $article->translate('fr', false)->setTitle($data['title']['fr']);
        $article->translate('en', false)->setText($data['text']['en']);
        $article->translate('fr', false)->setText($data['text']['fr']);

        $article->setAuthor($data['author']);
        $article->mergeNewTranslations();

        $this->_em->persist($article);
        $this->_em->flush();
    }
}
