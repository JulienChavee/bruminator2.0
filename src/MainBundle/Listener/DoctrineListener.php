<?php

namespace MainBundle\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use LogBundle\Entity\ActionLog;
use MatchBundle\Entity\Matchs;
use NotificationBundle\Entity\Notification;
use MatchBundle\Entity\MatchResult;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use TeamBundle\Entity\PlayerHistory;
use TeamBundle\Entity\Team;
use TeamBundle\Entity\Player;

class DoctrineListener
{
    private $changes;
    private $tokenStorage;

    public function __construct( TokenStorage $tokenStorage ) {
        $this->tokenStorage = $tokenStorage;
    }

    public function preUpdate( PreUpdateEventArgs $args ) {
        $this->changes = $args->getEntityChangeSet();
    }

    public function onFlush( OnFlushEventArgs $args ) {
        foreach ($args->getEntityManager()->getUnitOfWork()->getScheduledEntityInsertions() as $entity) {
            switch (true) {
                case $entity instanceof Notification:
                    if( !$args->getEntityManager()->getRepository( 'AdminBundle:Config' )->getOneBy( array( 'name' => 'send_notification' ) ) )
                        $args->getEntityManager()->detach( $entity );
                    break;

                case $entity instanceof MatchResult:
                    if( $entity->getMatch()->getType() == 'Match de barrage' ) {
                        $args->getEntityManager()->getEventManager()->removeEventListener( 'onFlush', $this ); // Evite une infinite loop à cause du flush() plus tard

                        $match = $args->getEntityManager()->getRepository( 'MatchBundle:Matchs' )->findMatchAfterBarrage( $args->getEntityManager()->getRepository( 'MainBundle:Edition' )->findLastEdition() );

                        if( $match->getAttack() == null )
                            $match->setAttack( $entity->getWinner() );
                        else
                            $match->setDefense( $entity->getWinner() );

                        $args->getEntityManager()->flush();
                    }
                    break;
            }
        }
    }

    public function postUpdate( LifecycleEventArgs $args ) {
        $entity = $args->getEntity();
        $em = $args->getEntityManager();

        switch( true ) {
            case $entity instanceof Team:
                foreach( $this->changes as $k => $v ) {
                    $action = array(
                        'value' => 'Modification de "%s" (de %s à %s)',
                        'parameters' => array( $k, $v[0], $v[1] )
                    );
                    $em->persist( $this->createLog( $action, $entity ) );

                    $em->flush();
                }
                break;

            case $entity instanceof Matchs:
                foreach( $this->changes as $k => $v ) {
                    $action = array(
                        'value' => 'Modification de "%s" (de %s à %s)',
                        'parameters' => array( $k, $v[0], $v[1] )
                    );
                    $em->persist( $this->createLog( $action, $entity ) );

                    foreach( $entity->getAttack()->getManagers() as $k2 => $v2){
                        switch( $k ) {
                            case 'date':
                                $em->persist( $this->createNotification( 'Nouvelle date ('.($v[1] ? $v[1]->format("d-m-Y à H:i") : "aucune date").') du match contre '.$entity->getDefense()->getName().' (ancienne date : '.($v[0] ? $v[0]->format("d-m-Y à H:i") : "aucune date").')', $v2 ) );
                                break;
                        }
                    }

                    foreach( $entity->getDefense()->getManagers() as $k2 => $v2){
                        switch( $k ) {
                            case 'date':
                                $em->persist( $this->createNotification( 'Nouvelle date ('.($v[1] ? $v[1]->format("d-m-Y à H:i") : "aucune date").') du match contre '.$entity->getAttack()->getName().' (ancienne date : '.($v[0] ? $v[0]->format("d-m-Y à H:i") : "aucune date").')', $v2 ) );
                                break;
                        }
                    }

                    if( $entity->getArbitre() ) {
                        switch( $k ) {
                            case 'date':
                                $em->persist( $this->createNotification( 'Nouvelle date (' . ($v[1] ? $v[1]->format("d-m-Y à H:i") : "aucune date") . ') du match ' . $entity->getAttack()->getName() . ' contre ' . $entity->getDefense()->getName() . ' que vous arbitrez (ancienne date : ' . ($v[0] ? $v[0]->format("d-m-Y à H:i") : "aucune date") . ')', $entity->getArbitre() ) );
                                break;
                        }
                    }

                    $em->flush();
                }
                break;

            case $entity instanceof Player:
                foreach( $this->changes as $k => $v ) {
                    if( $k == 'class' ) {
                        $playerHistory = new PlayerHistory();
                        $playerHistory->setDate( new \DateTime() );
                        $playerHistory->setPlayer( $entity );
                        $playerHistory->setPreviousClass( $v[1] );
                        $playerHistory->setEdition( $em->getRepository( 'MainBundle:Edition' )->findLastEdition() );

                        $em->persist( $playerHistory );
                    }

                    $action = array(
                        'value' => 'Modification de "%s" (de %s à %s)',
                        'parameters' => array( $k, $v[0], $v[1] )
                    );
                    $em->persist( $this->createLog( $action, $entity ) );

                    $em->flush();
                }
                break;

            default:
                $em->flush();
        }

        // HACK : http://stackoverflow.com/a/23673809
        // Should be fixed in doctrine 2.6.0 : https://github.com/doctrine/doctrine2/issues/3468
        $em->clear();
    }

    private function createLog( $action, $entity ) {
        $log = new ActionLog();

        $log->setDate( new \DateTime() );
        $log->setAction( json_encode( $action ) );
        $log->setIpAddress( $_SERVER[ 'REMOTE_ADDR' ] );
        $log->setClass( get_class( $entity ) );
        $log->setItemId( $entity->getId() );
        $log->setUser( $this->tokenStorage->getToken()->getUser() );

        return $log;
    }

    private function createNotification( $message, $user ) {
        $notification = new Notification();

        $notification->setMessage( $message );
        $notification->setUser( $user );
        $notification->setTimeCreated( new \Datetime() );
        $notification->setTimeRead( null );

        return $notification;
    }
}