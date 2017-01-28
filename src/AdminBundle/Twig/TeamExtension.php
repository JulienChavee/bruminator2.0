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

    public function getName() {
        return 'team_extension';
    }
}