<?php

namespace TeamBundle\Utils;

class ControlTeam {

    protected $em;

    public function __construct( $em )
    {
        $this->em = $em;
    }

    public function checkCompo( $players ) {
        $errors = array();

        $configTournoi = json_decode( $this->em->getRepository( 'AdminBundle:Config' )->getOneBy( array( 'name' => 'composition_team' ) ) );

        $errors = array_merge( $errors, $this->checkNumberPlayers( $players ) );

        if( in_array( 'pillier', $configTournoi ) )
            $errors = array_merge( $errors, $this->checkPillier( $players ) );
        if( in_array( 'banned_classed', $configTournoi ) )
            $errors = array_merge( $errors, $this->checkBannedClass( $players ) );
        if( in_array( 'doublon', $configTournoi ) )
            $errors = array_merge( $errors, $this->checkDoublon( $players ) );
        if( in_array( 'synergie', $configTournoi ) )
            $errors = array_merge( $errors, $this->checkSynergie( $players ) );

        return $errors;
    }

    private function checkPillier( $players ) {
        $errors = array();
        $pilliers = 0;

        foreach( $players as $k => $v ) {

            if( !$v->getIsRemplacant() && $v->getClass()->getIsPillier() )
                $pilliers++;
        }

        if( $pilliers > 1 )
            $errors[][ 'message' ] = "Vous ne pouvez pas avoir plus d'une classe pillier";

        return $errors;
    }

    private function checkBannedClass( $players ) {
        $errors = array();
        $bannedClass = 0;
        $classes = array();

        foreach( $players as $k => $v ) {
            if( !$v->getIsRemplacant() ) {
                $tempClassBanned = json_decode( $v->getClass()->getBannedClass() );
                $classes[] = $v->getClass();

                if( !is_null( $tempClassBanned ) ) {
                    foreach( $tempClassBanned as $k2 => $v2 ) {
                        $temp = $this->em->getRepository('TeamBundle:Classe')->findOneBy( array( 'id' => $v2 ) );

                        if( in_array( $temp, $classes ) )
                            $bannedClass++;
                    }
                }
            }
        }

        if( $bannedClass > 0 )
            $errors[][ 'message' ] = "Vous avez des classes ne pouvant pas être jouées ensemble";

        return $errors;
    }

    private function checkSynergie( $players ) {
        $errors = array();
        $synergieTotale = 0;

        foreach( $players as $k => $v ) {
            if( $v->getIsRemplacant() )
                unset( $players[$k] );
        }

        foreach( $players as $k => $v ) {
            $class = $v->getClass();

            foreach( $players as $k2 => $v2 ) {
                if( $k < $k2 )
                    $synergieTotale += $this->em->getRepository( 'TeamBundle:SynergieClass' )->getSynergie( $class->getId(), $v2->getClass()->getId() );
            }

            $synergieTotale += $class->getPoints();
        }

        if( $synergieTotale > $this->em->getRepository( 'AdminBundle:Config' )->getOneBy( array( 'name' => 'synergie_max' ) ) )
            $errors[]['message'] = "Votre composition a une synergie trop forte ($synergieTotale)";

        return $errors;
    }

    private function checkDoublon( $players ) {
        $errors = array();
        $classes = array();

        foreach( $players as $k => $v ) {
            if( !$v->getIsRemplacant() )
                $classes[] = $v->getClass();
        }

        if( count( array_unique( $classes, SORT_REGULAR ) ) < count( $classes ) )
            $errors[][ 'message' ] = "Vous ne pouvez pas avoir de classe doublon";

        return $errors;
    }

    private function checkNumberPlayers( $players ) {
        $errors = array();

        $nbPlayer = (int)$this->em->getRepository( 'AdminBundle:Config' )->getOneBy( array( 'name' => 'nb_players_team' ) );

        $i = 0;
        foreach( $players as $player ) {
            if( !$player->getIsRemplacant() )
                $i++;
        }

        if( $i != $nbPlayer )
            $errors[][ 'message' ] = "Le nombre de joueur dans l'équipe est incorrect (".$i."/".$nbPlayer.")";

        return $errors;
    }

    public function getPoints( $team, $isAdversaire ) {
        $matchs = $this->em->getRepository( 'MatchBundle:Matchs' )->findByTeam( $team->getId(), false, true );
        $points[ 'nb_match' ] = 0;
        $points[ 'pointsSuisse' ] = 0;
        $points[ 'pointsGoulta' ] = 0;
        $points[ 'pointsSuisseAdverse' ] = 0;
        $points[ 'pointsGoultaAdverse' ] = 0;

        foreach( $matchs as $k => $v ) {
            if( count( $v->getMatchResult() ) > 0 ) {
                $res = $v->getPoints( $team );
                $points[ 'nb_match' ]++;
                $points[ 'pointsSuisse' ] += $res[ 'pointsSuisse' ];
                $points[ 'pointsGoulta' ] += $res[ 'pointsGoulta' ];

                if( !$isAdversaire ) {
                    $pointsAdversaire = $this->getPoints( $v->getAttack() == $team ? $v->getDefense() : $v->getAttack(), true);

                    $points[ 'pointsSuisseAdverse' ] += $pointsAdversaire[ 'pointsSuisse' ];
                    $points[ 'pointsGoultaAdverse' ] += $pointsAdversaire[ 'pointsGoulta' ];
                }
            }
        }

        return $points;
    }

    public function getClassement( $teams ) {
        $classement = array();

        foreach( $teams as $k => $v ) {
            $res = $this->getPoints( $v, false );

            $classement[ 'team' ][ $k ] = $v;
            $classement[ 'nb_match' ][ $k ] = $res[ 'nb_match' ];
            $classement[ 'pointsSuisse' ][ $k ] = $res[ 'pointsSuisse' ];
            $classement[ 'pointsGoulta' ][ $k ] = $res[ 'pointsGoulta' ];
            $classement[ 'pointsSuisseAdverse' ][ $k ] = $res[ 'pointsSuisseAdverse' ];
            $classement[ 'pointsGoultaAdverse' ][ $k ] = $res[ 'pointsGoultaAdverse' ];
        }
        array_multisort( $classement[ 'pointsSuisse' ], SORT_DESC, $classement[ 'pointsSuisseAdverse' ], SORT_DESC, $classement[ 'pointsGoulta' ], SORT_DESC, $classement[ 'pointsGoultaAdverse' ], SORT_DESC, $classement[ 'nb_match' ], SORT_DESC, $classement[ 'team' ], SORT_DESC);

        return $classement;
    }
}