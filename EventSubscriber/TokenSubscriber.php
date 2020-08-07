<?php
namespace ProjetNormandie\ArticleBundle\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use ProjetNormandie\ArticleBundle\Entity\Comment;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;


final class TokenSubscriber implements EventSubscriberInterface
{

    private $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ['setUser', EventPriorities::PRE_VALIDATE],
        ];
    }

    /**
     * @param GetResponseForControllerResultEvent $event
     */
    public function setUser(GetResponseForControllerResultEvent $event)
    {
        $object = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        if (($object instanceof Comment) && in_array($method, array(Request::METHOD_POST))) {
            $object->setUser($this->tokenStorage->getToken()->getUser());
        }
    }
}
