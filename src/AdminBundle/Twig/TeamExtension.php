<?php

namespace AdminBundle\Twig;

use Symfony\Bridge\Doctrine\RegistryInterface;
use TeamBundle\Utils\ControlTeam;

class TeamExtension extends \Twig_Extension {

    protected $doctrine;
    protected $controlTeam;

    public function __construct( RegistryInterface $doctrine, ControlTeam $controlTeam )
    {
        $this->doctrine = $doctrine;
        $this->controlTeam = $controlTeam;
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction( 'checkCompo', array( $this, 'checkCompo' ) ),
            new \Twig_SimpleFunction( 'getPlayer', array( $this, 'getPlayer' ) ),
            new \Twig_SimpleFunction( 'getPlayerHistory', array( $this, 'getPlayerHistory' ) )
        );
    }

    public function checkCompo( $team ) {
        try {
            $errors = $this->controlTeam->checkCompo( $team->getPlayers() );

            return $errors;
        } catch( \Exception $e ) {
            return null;
        }
    }

    public function getPlayer( $id ) {
        $em = $this->doctrine->getManager();

        $value = $em->getRepository( 'TeamBundle:Player' )->findOneBy( array( 'id' => $id ) );

        return $value;
    }

    public function getPlayerHistory( $player, $edition = null ) {
        $em = $this->doctrine->getManager();

       if( is_null( $edition ) )
           $edition = $em->getRepository( 'MainBundle:Edition' )->findLastEdition();

       $player = $em->getRepository( 'TeamBundle:PlayerHistory' )->retrievePlayerClass( $player, $edition ) ? $em->getRepository( 'TeamBundle:PlayerHistory' )->retrievePlayerClass( $player, $edition )->getPlayer() : $player;

        return $player;
    }

    public function getName() {
        return 'team_extension';
    }
}