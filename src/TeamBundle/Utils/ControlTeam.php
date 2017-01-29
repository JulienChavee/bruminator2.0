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

        // TODO : Tenir compte du type de tournoi pour faire les check automatiquement
        //$errors = array_merge( $errors, $this->checkPillier( $players ) );
        //$errors = array_merge( $errors, $this->checkBannedClass( $players ) );
        $errors = array_merge( $errors, $this->checkDoublon( $players ) );
        if( count( $errors ) == 0 )
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

    public function getPoints( $team, $isAdversaire ) {
        $matchs = $this->em->getRepository( 'MatchBundle:Matchs' )->findByTeam( $team->getId() );
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
}