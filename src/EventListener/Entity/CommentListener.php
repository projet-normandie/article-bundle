<?php

declare(strict_types=1);

namespace ProjetNormandie\ArticleBundle\EventListener\Entity;

use Doctrine\Persistence\Event\LifecycleEventArgs;
use ProjetNormandie\ArticleBundle\Entity\Comment;
use Symfony\Bundle\SecurityBundle\Security;

class CommentListener
{
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * @param Comment $comment
     * @param LifecycleEventArgs $event
     */
    public function prePersist(Comment $comment, LifecycleEventArgs $event): void
    {
        $comment->setUser($this->security->getUser());
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
