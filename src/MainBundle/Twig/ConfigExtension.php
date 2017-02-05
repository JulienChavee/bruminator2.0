<?php

namespace MainBundle\Twig;

use Symfony\Bridge\Doctrine\RegistryInterface;

class ConfigExtension extends \Twig_Extension {

    protected $doctrine;

    public function __construct( RegistryInterface $doctrine )
    {
        $this->doctrine = $doctrine;
    }

    public function getFilters() {
        return array(
            new \Twig_SimpleFilter( 'config', array( $this, 'getValue' ) )
        );
    }

    public function getFunctions() {
        return array(
            new \Twig_SimpleFunction( 'isEndRonde', array( $this, 'isEndRonde' ) ),
            new \Twig_SimpleFunction( 'startFinal', array( $this, 'startFinal' ) )
        );
    }

    public function getValue( $name ) {
        $em = $this->doctrine->getManager();

        $value = $em->getRepository( 'AdminBundle:Config' )->getOneBy( array( 'name' => $name ) );

        if( json_decode( $value ) )
            return json_decode( $value, true );
        else
            return $value;
    }

    public function isEndRonde() {
        $em = $this->doctrine->getManager();

        $rondes = json_decode( $em->getRepository( 'AdminBundle:Config' )->getOneBy( array( 'name' => 'rondes' ) ) );
        $ronde = json_decode( $em->getRepository( 'AdminBundle:Config' )->getOneBy( array( 'name' => 'ronde'.$rondes->ronde_actuelle ) ) );

        if( $rondes->ronde_actuelle < $rondes->total ) {
            $now = new \DateTime( 'now' );
            $endRonde = \DateTime::createFromFormat( 'Y-m-d', $ronde->end_date );

            if ( $now >= $endRonde )
                return true;
            else
                return false;
        } else
            return false;
    }

    public function startFinal() {
        $em = $this->doctrine->getManager();

        $rondes = json_decode( $em->getRepository( 'AdminBundle:Config' )->getOneBy( array( 'name' => 'rondes' ) ) );
        $ronde = json_decode( $em->getRepository( 'AdminBundle:Config' )->getOneBy( array( 'name' => 'ronde'.$rondes->total ) ) );

        if( $rondes->ronde_actuelle == $rondes->total ) {
            $now = new \DateTime( 'now' );
            $endRonde = \DateTime::createFromFormat( 'Y-m-d', $ronde->end_date );

            $matchs = $em->getRepository( 'MatchBundle:Matchs' )->getBy( array( 'type' => 'Quart de finale' ) );

            if ( $now >= $endRonde && count( $matchs ) == 0 )
                return true;
            else
                return false;
        } else
            return false;
    }

    public function getName() {
        return 'config_extension';
    }
}
