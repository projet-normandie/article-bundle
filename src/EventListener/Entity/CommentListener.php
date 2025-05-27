<?php

declare(strict_types=1);

namespace ProjetNormandie\ArticleBundle\EventListener\Entity;

use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use HTMLPurifier;
use HTMLPurifier_Config;
use ProjetNormandie\ArticleBundle\Entity\Comment;
use Symfony\Bundle\SecurityBundle\Security;

class CommentListener
{
    private HTMLPurifier $purifier;

    public function __construct(
        private readonly Security $security,
    ) {
        $config = HTMLPurifier_Config::createDefault();
        $config->set('HTML.Allowed', 'p,br,strong,em,u,ol,ul,li,a[href],h1,h2,h3,blockquote');
        $this->purifier = new HTMLPurifier($config);
    }

    /**
     * @param Comment $comment
     * @param LifecycleEventArgs $event
     */
    public function prePersist(Comment $comment, LifecycleEventArgs $event): void
    {
        $comment->setUser($this->security->getUser());
        $comment->getArticle()->setNbComment($comment->getArticle()->getNbComment() + 1);
        $this->purifyContent($comment);
    }

    public function preUpdate(Comment $comment, PreUpdateEventArgs $event): void
    {
        $this->purifyContent($comment);
    }


    /**
     * @param Comment $comment
     * @param LifecycleEventArgs $event
     */
    public function preRemove(Comment $comment, LifecycleEventArgs $event): void
    {
        $comment->getArticle()->setNbComment($comment->getArticle()->getNbComment() - 1);
    }



    private function purifyContent(Comment $comment): void
    {
        if ($comment->getContent()) {
            $comment->setContent($this->purifier->purify($comment->getContent()));
        }
    }
}
