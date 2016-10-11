<?php

namespace LogBundle\Utils;


use LogBundle\Entity\ActionLog;

class Log
{
    protected $em;

    public function __construct( $em )
    {
        $this->em = $em;
    }

    public function Log( $user, $action, $ip, $class, $item ) {
        $actionLog = new ActionLog();

        $actionLog->setDate( new \DateTime() );
        $actionLog->setUser( $user );
        $actionLog->setAction( $action );
        $actionLog->setIpAddress( $ip );
        $actionLog->setClass( $class );
        $actionLog->setItemId( $item ? $item->getId() : NULL );

        $this->em->persist( $actionLog );
        $this->em->flush();
    }
}