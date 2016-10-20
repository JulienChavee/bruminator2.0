<?php

namespace MatchBundle\Utils;

use Doctrine\ORM\EntityManager;
use MatchBundle\Entity\Matchs;
use TeamBundle\Utils\ControlTeam;

class ControlMatch
{
    protected $em;
    protected $controlTeam;
    private $typeTournoi;

    public function __construct( EntityManager $em, ControlTeam $controlTeam ) {
        $this->em = $em;
        $this->typeTournoi = $this->em->getRepository( 'AdminBundle:Config' )->getOneBy( array( 'name' => 'type_tournoi' ) );
        $this->controlTeam = $controlTeam;
    }

    public function matchCreated() {
        if( $this->typeTournoi == 'ronde' ) {
            $rondes = json_decode( $this->em->getRepository( 'AdminBundle:Config' )->getOneBy( array( 'name' => 'rondes' ) ), true );

            if( $rondes['ronde_actuelle'] === 0 )
                return false;
            else
                return true;
        }
    }

    public function generateMatch() {
        if( $this->typeTournoi == 'ronde' ) {
            return $this->generateNextRonde();
        }
    }

    private function generateNextRonde() {
        $teams = $this->em->getRepository( 'TeamBundle:Team' )->findBy( array( 'valid' => 1 ), array( 'inscriptionDate' => 'asc' ) );
        $config =  $this->em->getRepository( 'AdminBundle:Config' )->findOneBy( array( 'name' => 'rondes' ) );
        $rondes = json_decode( $config->getValue(), true );

        if( $rondes['ronde_actuelle'] === 0 ) {
            try {
                $total = count( $teams );

                if( $total % 2 != 0 ) {
                    $return = $this->generateMatchBarrage( $teams );
                    $teams = $return[ 'teams' ];

                    $this->em->persist( $return[ 'match' ] );

                    $total = count( $teams );
                }

                $teamsSelected = array();
                for ( $i = 0; $i < $total / 2; $i++ ) {
                    do {
                        $attack = rand( 0, ($total - 1) );
                    } while ( in_array( $attack, $teamsSelected ) );

                    do {
                        $def = rand( 0, ($total - 1) );
                    } while ( in_array( $def, $teamsSelected ) || $def == $attack );

                    $match = new Matchs();
                    $match->setAttack( $teams[ $attack ] );
                    $match->setDefense( $teams[ $def ] );
                    $match->setDate( NULL );
                    $match->setArbitre( NULL );
                    $match->setType( 'Ronde 1' );

                    $this->em->persist( $match );

                    array_push( $teamsSelected, $attack, $def );
                }

                $rondes[ 'ronde_actuelle'] = 1;
                $config->setValue( json_encode( $rondes ) );

                $this->em->flush();
            }
            catch( \Exception $e ) {
                return array( 'status' => 'ko', 'message' => 'Une erreur inconnue s\'est produite', 'debug' => $e->getMessage() );
            }

            return array( 'status' => 'ok' );
        } else {
            try {
                $classement = array();

                foreach ($teams as $k => $v) {
                    $res = $this->controlTeam->getPoints($v, false);

                    $classement['team'][$k] = $v;
                    $classement['nb_match'][$k] = $res['nb_match'];
                    $classement['pointsSuisse'][$k] = $res['pointsSuisse'];
                    $classement['pointsGoulta'][$k] = $res['pointsGoulta'];
                    $classement['pointsSuisseAdverse'][$k] = $res['pointsSuisseAdverse'];
                    $classement['pointsGoultaAdverse'][$k] = $res['pointsGoultaAdverse'];
                }
                array_multisort($classement['pointsSuisse'], SORT_DESC, $classement['pointsGoulta'], SORT_DESC, $classement['pointsSuisseAdverse'], SORT_DESC, $classement['pointsGoultaAdverse'], SORT_DESC, $classement['nb_match'], SORT_DESC, $classement['team'], SORT_DESC);

                $i = 0;

                while ($i < count($teams)) {
                    $match = new Matchs();
                    $match->setAttack($classement[ 'team' ][$i]);
                    $match->setDefense($classement[ 'team' ][$i + 1]);
                    $match->setDate(NULL);
                    $match->setArbitre(NULL);
                    $match->setType('Ronde ' . ($rondes[ 'ronde_actuelle' ] + 1) );

                    $this->em->persist($match);

                    $i += 2;
                }

                $rondes['ronde_actuelle'] = 2;
                $config->setValue(json_encode($rondes));

                $this->em->flush();
            }
            catch( \Exception $e ) {
                return array( 'status' => 'ko', 'message' => 'Une erreur inconnue s\'est produite', 'debug' => $e->getMessage() );
            }

            return array( 'status' => 'ok' );
        }
    }

    private function generateMatchBarrage( $teams ) {
        $match = new Matchs();

        if ( rand( 0, 1 ) === 0 ) {
            $match->setAttack( array_slice( $teams, -1, 1 )[ 0 ] );
            $match->setDefense( array_slice( $teams, -2, 1 )[ 0 ] );
        } else {
            $match->setAttack( array_slice( $teams, -2, 1 )[ 0 ] );
            $match->setDefense( array_slice( $teams, -1, 1 )[ 0 ] );
        }

        $match->setDate( NULL );
        $match->setArbitre( NULL );
        $match->setType( 'Match de barrage' );

        $teams = array_slice( $teams, 0, count( $teams ) - 3 );

        return array( 'match' => $match, 'teams' => $teams );
    }
}