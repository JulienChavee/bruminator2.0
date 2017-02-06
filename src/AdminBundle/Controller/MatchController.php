<?php

namespace AdminBundle\Controller;

use MatchBundle\Entity\MatchResult;
use MatchBundle\Entity\MatchResultTeam;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class MatchController extends Controller
{
    /**
     * @Route("/match", name="admin_match")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $nbTeam = count($em->getRepository( 'TeamBundle:Team' )->findAll());
        $nbTeamValid = count($em->getRepository( 'TeamBundle:Team' )->findBy( array( 'valid' => 1 ) ) );
        $date = \DateTime::createFromFormat( 'Y-m-d H:i:s', $em->getRepository( 'AdminBundle:Config' )->getOneBy( array( 'name' => 'inscription_end' ) ) );
        $matchs = $em->getRepository( 'MatchBundle:Matchs' )->findByDate( $date, array( 'field' => 'date', 'type' => 'DESC' ), null, false );

        return $this->render( 'AdminBundle:Match:index.html.twig', array( 'matchs' => $matchs, 'nbTeam' => $nbTeam, 'nbTeamValid' => $nbTeamValid ) );
    }

    /**
     * @Route("/match/feuille/{id}", name="admin_match_feuille")
     */
    public function feuilleAction( $id ) {
        $em = $this->getDoctrine()->getManager();

        $match = $em->getRepository( 'MatchBundle:Matchs' )->findOneBy( array( 'id' => $id ) );

        return $this->render( 'AdminBundle:Match:feuilleMatch.html.twig', array( 'match' => $match ) );
    }

    /**
     * @Route("/match/ajax/generate", name="admin_match_ajax_generate")
     */
    public function ajaxGenerateMatchAction( Request $request ) {
        if( $request->isXmlHttpRequest() ) {
            $response = new Response( json_encode( $this->get( 'match.control_match' )->generateMatch() ) );
            $response->headers->set( 'Content-Type', 'application/json' );

            return $response;
        }

        $response = new Response( json_encode( array( 'status' => 'ko', 'message' => 'Accès refusé', 'debug' => 'Bad request' ) ) );
        $response->headers->set( 'Content-Type', 'application/json') ;
        return $response;
    }

    /**
     * @Route("/match/ajax/editdate", name="admin_match_ajax_edit_date")
     */
    public function ajaxEditDateAction( Request $request ) {
        if( $request->isXmlHttpRequest() ) {
            try {
                $em = $this->getDoctrine()->getManager();

                $match = $em->getRepository( 'MatchBundle:Matchs' )->findOneBy( array( 'id' => $request->get( 'id' ) ) );

                if( $match ) {
                    $match->setDate( $request->get( 'date' ) == '' ? NULL : \DateTime::createFromFormat( 'd/m/Y H:i', $request->get( 'date' ) ) );

                    $em->flush();

                    $response = new Response( json_encode( array( 'status' => 'ok', 'return' => $this->render( 'AdminBundle:Match:matchRow.html.twig', array( 'match' => $match ) )->getContent() ) ) );
                } else
                    $response = new Response( json_encode( array( 'status' => 'ko', 'message' => 'Ce match est introuvable' ) ) );
            }
            catch( \Exception $e ) {
                $response = new Response( json_encode( array( 'status' => 'ko', 'message' => 'Une erreur inconnue s\'est produite', 'debug' => $e->getMessage() ) ) );
            }
            $response->headers->set( 'Content-Type', 'application/json' );
            return $response;
        }

        $response = new Response( json_encode( array( 'status' => 'ko', 'message' => 'Accès refusé', 'debug' => 'Bad request' ) ) );
        $response->headers->set( 'Content-Type', 'application/json') ;
        return $response;
    }

    /**
     * @Route("/match/ajax/getdate", name="admin_match_ajax_get_date")
     */
    public function ajaxGetDateAction( Request $request ) {
        if( $request->isXmlHttpRequest() ) {
            try {
                $em = $this->getDoctrine()->getManager();

                $match = $em->getRepository( 'MatchBundle:Matchs' )->findOneBy( array( 'id' => $request->get( 'id' ) ) );

                if( $match )
                    $response = new Response( json_encode( array( 'status' => 'ok', 'return' => $match->getDate() ) ) );
                else
                    $response = new Response( json_encode( array( 'status' => 'ko', 'message' => 'Ce match est introuvable' ) ) );
            }
            catch( \Exception $e ) {
                $response = new Response( json_encode( array( 'status' => 'ko', 'message' => 'Une erreur inconnue s\'est produite', 'debug' => $e->getMessage() ) ) );
            }
            $response->headers->set( 'Content-Type', 'application/json' );
            return $response;
        }

        $response = new Response( json_encode( array( 'status' => 'ko', 'message' => 'Accès refusé', 'debug' => 'Bad request' ) ) );
        $response->headers->set( 'Content-Type', 'application/json') ;
        return $response;
    }

    /**
     * @Route("/match/ajax/editarbitre", name="admin_match_ajax_edit_arbitre")
     */
    public function ajaxEditArbitreAction( Request $request ) {
        if( $request->isXmlHttpRequest() ) {
            try {
                $em = $this->getDoctrine()->getManager();

                $match = $em->getRepository( 'MatchBundle:Matchs' )->findOneBy( array( 'id' => $request->get( 'id' ) ) );

                if( $match ) {
                    $match->setArbitre( $request->get( 'arbitre' ) == '' ? NULL : $em->getRepository( 'UserBundle:User' )->findOneBy( array( 'id' => $request->get( 'arbitre' ) ) ) );

                    $em->flush();

                    $response = new Response( json_encode( array( 'status' => 'ok', 'return' => $this->render( 'AdminBundle:Match:matchRow.html.twig', array( 'match' => $match ) )->getContent() ) ) );
                } else
                    $response = new Response( json_encode( array( 'status' => 'ko', 'message' => 'Ce match est introuvable' ) ) );
            }
            catch( \Exception $e ) {
                $response = new Response( json_encode( array( 'status' => 'ko', 'message' => 'Une erreur inconnue s\'est produite', 'debug' => $e->getMessage() ) ) );
            }
            $response->headers->set( 'Content-Type', 'application/json' );
            return $response;
        }

        $response = new Response( json_encode( array( 'status' => 'ko', 'message' => 'Accès refusé', 'debug' => 'Bad request' ) ) );
        $response->headers->set( 'Content-Type', 'application/json') ;
        return $response;
    }

    /**
     * @Route("/match/ajax/getArbitre", name="admin_match_ajax_get_arbitre")
     */
    public function ajaxGetArbitreAction( Request $request ) {
        if( $request->isXmlHttpRequest() ) {
            try {
                $em = $this->getDoctrine()->getManager();

                $match = $em->getRepository( 'MatchBundle:Matchs' )->findOneBy( array( 'id' => $request->get( 'id' ) ) );
                $listArbitres = $em->getRepository( 'UserBundle:User' )->findByRole( 'ROLE_ADMIN' );
                $listArbitres = array_merge( $em->getRepository( 'UserBundle:User' )->findByRole( 'ROLE_SUPER_ADMIN' ), $listArbitres );

                $normalizer  = new ObjectNormalizer();;
                $normalizer->setCircularReferenceHandler(function ($object) {
                    return $object->getId();
                });
                $serializer = new Serializer( array( $normalizer ) );
                $listArbitres = $serializer->normalize( $listArbitres );

                $arbitre = $serializer->normalize( $match->getArbitre() );

                if( $match )
                    $response = new Response( json_encode( array( 'status' => 'ok', 'return' => $arbitre, 'listArbitres' => $listArbitres ) ) );
                else
                    $response = new Response( json_encode( array( 'status' => 'ko', 'message' => 'Ce match est introuvable' ) ) );
            }
            catch( \Exception $e ) {
                $response = new Response( json_encode( array( 'status' => 'ko', 'message' => 'Une erreur inconnue s\'est produite', 'debug' => $e->getMessage() ) ) );
            }
            $response->headers->set( 'Content-Type', 'application/json' );
            return $response;
        }

        $response = new Response( json_encode( array( 'status' => 'ko', 'message' => 'Accès refusé', 'debug' => 'Bad request' ) ) );
        $response->headers->set( 'Content-Type', 'application/json') ;
        return $response;
    }

    /**
     * @Route("/match/ajax/updatefeuille", name="admin_match_ajax_update_feuille")
     */
    public function ajaxUpdateFeuilleAction( Request $request ) {
        if( $request->isXmlHttpRequest() ) {
            try {
                $em = $this->getDoctrine()->getManager();

                $matchData = json_decode( $request->get( 'match' ), true );
                $teamAttackData = json_decode( $request->get( 'attack' ), true );
                $teamDefenseData = json_decode( $request->get( 'defense' ), true );

                $forfait = $teamAttackData[ 'forfait' ] || $teamDefenseData[ 'forfait' ];

                $match = $em->getRepository( 'MatchBundle:Matchs' )->findOneBy( array( 'id' => $matchData[ 'id' ] ) );

                $feuille = $em->getRepository( 'MatchBundle:MatchResult' )->findOneBy( array( 'match' => $match->getId() ) );
                $feuilleTeamAttack = $feuille ? $em->getRepository( 'MatchBundle:MatchResultTeam' )->findOneBy( array( 'matchResult' => $feuille->getId(), 'team' => $match->getAttack()->getId() ) ) : null;
                $feuilleTeamDefense = $feuille ? $em->getRepository( 'MatchBundle:MatchResultTeam' )->findOneBy( array( 'matchResult' => $feuille->getId(), 'team' => $match->getDefense()->getId() ) ) : null;

                $newFeuille = false;

                if( !$feuille ){
                    $feuille = new MatchResult();
                    $feuilleTeamAttack = new MatchResultTeam();
                    $feuilleTeamDefense = new MatchResultTeam();

                    $newFeuille = true;
                }

                $feuille->setMatch( $match );
                $feuille->setFirst( $forfait ? null : $em->getRepository( 'TeamBundle:Team' )->findOneBy( array( 'id' => $matchData[ 'first_team' ] ) ) );
                $feuille->setNombreTour( $forfait ? null : $matchData[ 'nbTour' ] );
                $feuille->setWinner( $forfait ? ( $teamAttackData[ 'forfait' ] ? $match->getDefense() : $match->getAttack() ) : ( $teamAttackData[ 'morts' ] == 4 ? $match->getDefense() : $match->getAttack() ) );

                $feuilleTeamAttack->setMatchResult( $feuille );
                $feuilleTeamAttack->setTeam( $match->getAttack() );
                $feuilleTeamAttack->setNombreMort( $forfait ? null : $teamAttackData[ 'morts' ] );
                $feuilleTeamAttack->setInitiative( $forfait ? null : $teamAttackData[ 'ini' ] );
                $feuilleTeamAttack->setRetard( $teamAttackData[ 'retard' ] );
                $feuilleTeamAttack->setForfait( $teamAttackData[ 'forfait' ] );
                $feuilleTeamAttack->setPenalite( $teamAttackData[ 'penalite' ] );

                $feuilleTeamDefense->setMatchResult( $feuille );
                $feuilleTeamDefense->setTeam( $match->getDefense() );
                $feuilleTeamDefense->setNombreMort( $forfait ? null : $teamDefenseData[ 'morts' ] );
                $feuilleTeamDefense->setInitiative( $forfait ? null : $teamDefenseData[ 'ini' ] );
                $feuilleTeamDefense->setRetard( $teamDefenseData[ 'retard' ] );
                $feuilleTeamDefense->setForfait( $teamDefenseData[ 'forfait' ] );
                $feuilleTeamDefense->setPenalite( $teamDefenseData[ 'penalite' ] );

                if( $newFeuille ) {
                    $em->persist( $feuille );
                    $em->persist( $feuilleTeamAttack );
                    $em->persist( $feuilleTeamDefense );
                }

                $em->flush();

                $response = new Response( json_encode( array( 'status' => 'ok' ) ) );
            }
            catch( \Exception $e ) {
                $response = new Response( json_encode( array( 'status' => 'ko', 'message' => 'Une erreur inconnue s\'est produite', 'debug' => $e->getMessage().'('.$e->getFile().' : '.$e->getLine().')' ) ) );
            }
            $response->headers->set( 'Content-Type', 'application/json' );
            return $response;
        }

        $response = new Response( json_encode( array( 'status' => 'ko', 'message' => 'Accès refusé', 'debug' => 'Bad request' ) ) );
        $response->headers->set( 'Content-Type', 'application/json') ;
        return $response;
    }
}
