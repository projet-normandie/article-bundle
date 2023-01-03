<?php

namespace ProjetNormandie\ArticleBundle\EventListener\Entity;

use Doctrine\Persistence\Event\LifecycleEventArgs;
use ProjetNormandie\ArticleBundle\Entity\Comment;

class CommentListener
{

    /**
     * @param Comment $comment
     * @param LifecycleEventArgs $event
     */
    public function prePersist(Comment $comment, LifecycleEventArgs $event): void
    {
        $comment->getArticle()->setNbComment($comment->getArticle()->getNbComment() + 1);
    }


    /**
     * @param Comment $comment
     * @param LifecycleEventArgs $event
     */
    public function preRemove(Comment $comment, LifecycleEventArgs $event): void
    {
        $comment->getArticle()->setNbComment($comment->getArticle()->getNbComment() - 1);
    }
}
