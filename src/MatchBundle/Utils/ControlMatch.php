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
    private $rondes;

    public function __construct( EntityManager $em, ControlTeam $controlTeam ) {
        $this->em = $em;
        $this->typeTournoi = $this->em->getRepository( 'AdminBundle:Config' )->getOneBy( array( 'name' => 'type_tournoi' ) );
        $this->controlTeam = $controlTeam;
        $this->rondes = json_decode( $this->em->getRepository( 'AdminBundle:Config' )->getOneBy( array( 'name' => 'rondes' ) ), true );
    }

    public function matchCreated() {
        if( $this->typeTournoi == 'ronde' ) {
            if( $this->rondes['ronde_actuelle'] === 0 )
                return false;
            else
                return true;
        } else {
            // TODO Si le tournoi n'est pas à ronde
        }
    }

    public function generateMatch() {
        if( count( $this->em->getRepository( 'MatchBundle:Matchs' )->findMatchWithoutResult() ) == 0 ) {
            if( $this->typeTournoi == 'ronde' ) {
                if( $this->rondes[ 'ronde_actuelle' ] == $this->rondes[ 'total' ] )
                    return $this->generatePhaseFinale();
                else
                    return $this->generateNextRonde();
            } else {
                // TODO Si le tournoi n'est pas à ronde
            }
        } else
            return array('status' => 'ko', 'message' => 'Tous les matchs n\'ont pas un résultat');
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

                    $total = count( $teams ) - 1;
                }

                $teamsSelected = array();
                for ( $i = 0; $i < $total / 2; $i++ ) {
                    do {
                        $attack = rand( 0, $total );
                    } while( in_array( $attack, $teamsSelected ) );

                    do {
                        $def = rand( 0, $total );
                    } while( in_array( $def, $teamsSelected ) || $def == $attack );

                    $match = new Matchs();
                    $match->setAttack( $teams[ $attack ] );
                    $match->setDefense( $teams[ $def ] );
                    $match->setDate( NULL );
                    $match->setArbitre( NULL );
                    $match->setType( 'Ronde 1' );
                    $match->setEdition( $this->em->getRepository( 'MainBundle:Edition' )->findLastEdition() );

                    $this->em->persist( $match );

                    array_push( $teamsSelected, $attack, $def );
                }

                $missingTeam = array_values(array_diff(  range( 0, $total ), $teamsSelected ) );

                $match = new Matchs();
                $match->setAttack( $teams[ $missingTeam[ 0 ] ] );
                $match->setDefense( NULL );
                $match->setDate( NULL );
                $match->setArbitre( NULL );
                $match->setType( 'Ronde 1' );
                $match->setEdition( $this->em->getRepository( 'MainBundle:Edition' )->findLastEdition() );

                $this->em->persist( $match );

                $rondes[ 'ronde_actuelle'] = 1;
                $config->setValue( json_encode( $rondes ) );

                $this->em->flush();
            }
            catch( \Exception $e ) {
                return array( 'status' => 'ko', 'message' => 'Une erreur inconnue s\'est produite', 'debug' => $e->__toString() );
            }

            return array( 'status' => 'ok' );
        } else {
            try {
                // Si nombre d'équipe impair, on enlève l'équipe ayant perdu le match de barrage
                if( count( $teams ) % 2 > 0 ) {
                    $match = $this->em->getRepository( 'MatchBundle:Matchs' )->findOneBy( array( 'type' => 'Match de barrage' ) );

                    if( $match->getMatchResult()->getWinner() == $match->getAttack() )
                        $looser = $match->getDefense();
                    else
                        $looser = $match->getAttack();

                    unset( $teams[ array_search( $looser, $teams ) ] );
                    $teams = array_values( $teams );
                }

                $classement = $this->controlTeam->getClassement( $teams );

                while ( count( $classement[ 'team' ] ) > 0 ) {
                    $teamsSelected = $this->selectTeamWithSamePoints( $classement );
                    $nbTeams = count( $teamsSelected );

                    for( $i = 0; $i < $nbTeams / 2; $i++ ) {
                        $attack = rand( 0, count( $teamsSelected ) - 1 );

                        do {
                            $defense = rand( 0, count( $teamsSelected ) - 1 );
                        } while( $defense == $attack );

                        $match = new Matchs();
                        $match->setAttack( $teamsSelected[ $attack ][ 'team' ] );
                        $match->setDefense( $teamsSelected[ $defense ][ 'team' ] );
                        $match->setDate(NULL);
                        $match->setArbitre(NULL);
                        $match->setType('Ronde ' . ($rondes[ 'ronde_actuelle' ] + 1) );
                        $match->setEdition( $this->em->getRepository( 'MainBundle:Edition' )->findLastEdition() );

                        $this->em->persist($match);

                        unset( $classement[ 'team' ][ $teamsSelected[ $attack ][ 'key' ] ] );
                        unset( $classement[ 'pointsSuisse' ][ $teamsSelected[ $attack ][ 'key' ] ] );
                        unset( $classement[ 'team' ][ $teamsSelected[ $defense ][ 'key' ] ] );
                        unset( $classement[ 'pointsSuisse' ][ $teamsSelected[ $defense ][ 'key' ] ] );
                        unset( $teamsSelected[ $attack ] );
                        unset( $teamsSelected[ $defense ] );

                        $teamsSelected = array_values( $teamsSelected );
                    }

                    $classement[ 'team' ] = array_values( $classement[ 'team' ] );
                    $classement[ 'pointsSuisse' ] = array_values( $classement[ 'pointsSuisse' ] );
                }

                $rondes['ronde_actuelle'] += 1;
                $config->setValue(json_encode($rondes));

                $this->em->flush();
            }
            catch( \Exception $e ) {
                return array( 'status' => 'ko', 'message' => 'Une erreur inconnue s\'est produite', 'debug' => $e->getMessage() );
            }

            return array( 'status' => 'ok' );
        }
    }

    private function selectTeamWithSamePoints( $teams, $nombrePaireEquipe = true ) {
        $points = $teams[ 'pointsSuisse' ][ 0 ];
        $return = array();

        $teams_temp = $teams;

        for( $i = 0; $i < count( $teams ); $i++ ) {
            if( $teams[ 'pointsSuisse' ][ $i ] == $points ) {
                $return[] = array( 'team' => $teams[ 'team' ][ $i ], 'key' => $i );
                unset( $teams_temp[ 'team' ][ $i ] );
                unset( $teams_temp[ 'pointsSuisse' ][ $i ] );
            } else
                break;
        }

        if( $nombrePaireEquipe and count( $return ) % 2 > 0 ) {
            $teams_temp[ 'team' ] = array_values( $teams_temp[ 'team' ] );
            $teams_temp[ 'pointsSuisse' ] = array_values( $teams_temp[ 'pointsSuisse' ] );
            $teams_temp = $this->selectTeamWithSamePoints( $teams_temp, false );

            $rand = rand( 0, count( $teams_temp ) -1 );

            $return[] = array( 'team' => $teams[ 'team' ][ $i + $rand ], 'key' => ($i + $rand) );
        }

        return $return;
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
        $match->setEdition( $this->em->getRepository( 'MainBundle:Edition' )->findLastEdition() );

        $teams = array_slice( $teams, 0, count( $teams ) - 2 );

        return array( 'match' => $match, 'teams' => $teams );
    }

    private function generatePhaseFinale() {
        $teams = $this->em->getRepository( 'TeamBundle:Team' )->findBy( array( 'valid' => true ) );

        $classement = $this->controlTeam->getClassement( $teams );

        try {
            for ($i = 0; $i < 4; $i++) {
                $match = new Matchs();
                $match->setAttack( $classement[ 'team' ][ $i ] );
                $match->setDefense( $classement[ 'team' ][ ( 7 - $i ) ] );
                $match->setDate( NULL );
                $match->setArbitre( NULL );
                $match->setType( 'Quart de finale' );
                $match->setEdition( $this->em->getRepository( 'MainBundle:Edition' )->findLastEdition() );

                $this->em->persist( $match );
            }

            $this->em->flush();
        }
        catch( \Exception $e ) {
            return array( 'status' => 'ko', 'message' => 'Une erreur inconnue s\'est produite', 'debug' => $e->getMessage() );
        }

        return array( 'status' => 'ok' );
    }
}