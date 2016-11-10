<?php

namespace NotificationBundle\Utils;


use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class NotificationService
{
    protected $em;
    protected $tokenStorage;

    public function __construct( EntityManager $em, TokenStorage $tokenStorage )
    {
        $this->em = $em;
        $this->tokenStorage = $tokenStorage;
    }

    public function getNotifications(){
        return $this->em->getRepository( 'NotificationBundle:Notification' )->findBy( array( 'user' => $this->tokenStorage->getToken()->getUser() ), array( 'timeCreated' => 'DESC' ), 5 );
    }

    public function getCountNotifications(){
        return count($this->em->getRepository( 'NotificationBundle:Notification' )->findBy( array( 'user' => $this->tokenStorage->getToken()->getUser(), 'timeRead' => null ) ) );
    }
}