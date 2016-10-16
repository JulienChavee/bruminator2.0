<?php

namespace MatchBundle\Utils;

use Doctrine\ORM\EntityManager;
use MatchBundle\Entity\Matchs;

class ControlMatch
{
    protected $em;
    private $typeTournoi;

    public function __construct( EntityManager $em ) {
        $this->em = $em;
        $this->typeTournoi = $this->em->getRepository( 'AdminBundle:Config' )->getOneBy( array( 'name' => 'type_tournoi' ) );
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
                if ($total = count($teams) % 2 != 0) {
                    $match = new Matchs();

                    if (rand(0, 1) === 0) {
                        $match->setAttack(array_slice($teams, -1, 1)[0]);
                        $match->setDefense(array_slice($teams, -2, 1)[0]);
                    } else {
                        $match->setAttack(array_slice($teams, -2, 1)[0]);
                        $match->setDefense(array_slice($teams, -1, 1)[0]);
                    }

                    $match->setDate(NULL);
                    $match->setArbitre(NULL);
                    $match->setType('Match de barrage');

                    $this->em->persist($match);

                    $teams = array_slice($teams, 0, count($teams) - 3);
                    $total = count($teams) - 1;
                }

                $teamsSelected = array();
                for ($i = 0; $i < $total / 2; $i++) {
                    do {
                        $attack = rand(0, $total);
                    } while (in_array($attack, $teamsSelected));

                    do {
                        $def = rand(0, $total);
                    } while (in_array($def, $teamsSelected) || $def == $attack);

                    $match = new Matchs();
                    $match->setAttack($teams[$attack]);
                    $match->setDefense($teams[$def]);
                    $match->setDate(NULL);
                    $match->setArbitre(NULL);
                    $match->setType('Ronde 1');

                    $this->em->persist($match);

                    array_push($teamsSelected, $attack, $def);
                }

                $this->em->flush();

                $rondes['ronde_actuelle'] = 1;
                $config->setValue(json_encode($rondes));
            }
            catch( \Exception $e ) {
                return array( 'status' => 'ko', 'message' => 'Une erreur inconnue s\'est produite', 'debug' => $e->getMessage() );
            }

            return array( 'status' => 'ok' );
        }
    }
}