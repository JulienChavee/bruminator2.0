<?php

namespace TeamBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use TeamBundle\Entity\Player;

class PlayerController extends Controller
{
    /**
     * @Route("/player/ajax/get", name="team_player_ajax_get")
     */
    public function ajaxGetAction( Request $request ) {
        if( $request->isXmlHttpRequest() ) {
            try {
                $user = $this->getUser();

                $em = $this->getDoctrine()->getManager();
                $player = $em->getRepository( 'TeamBundle:Player' )->findOneBy( array( 'id' => $request->get( 'id' ) ) );
                if( $user->getTeam() == $player->getTeam() ) {
                    $normalizer = new ObjectNormalizer();;
                    $normalizer->setCircularReferenceHandler( function ( $object ) {
                        return $object->getId();
                    });
                    $serializer = new Serializer( array( $normalizer ) );
                    $player = $serializer->normalize($player);
                    $response = new Response( json_encode( array( 'status' => 'ok', 'player' => $player ) ) );
                } else
                    $response = new Response( json_encode( array( 'status' => 'ko', 'message' => 'Vous n\'avez pas la permission d\'éditer ce joueur', 'debug' => 'Utilisateur connecté != manager de l\'équipe du joueur' ) ) );
            }
            catch( \Exception $e ) {
                $response = new Response( json_encode( array( 'status' => 'ko', 'message' => 'Une erreur inconnue s\'est produite', 'debug' => $e->getMessage() ) ) );
            }
            $response->headers->set( 'Content-Type', 'application/json' );
            return $response;
        }
        $response = new Response( json_encode( array( 'status' => 'ko', 'message' => 'Accès non autorisé', 'debug' => 'Bad request' ) ) );
        $response->headers->set( 'Content-Type', 'application/json') ;
        return $response;
    }

    /**
     * @Route("/player/ajax/search", name="team_player_ajax_search")
     */
    public function ajaxSearchAction( Request $request ) {
        if( $request->isXmlHttpRequest() ) {
            $em = $this->getDoctrine()->getManager();

            $res = $em->getRepository( 'TeamBundle:Player' )->findByPseudoLike( $request->get( 'term' ), false );

            $pseudos = array();
            foreach( $res as $k => $v ) {
                $pseudos[] = $v['pseudo'];
            }

            $response = new Response( json_encode( $pseudos ) );
            $response->headers->set( 'Content-Type', 'application/json' );
            return $response;
        }
        $response = new Response( json_encode( array( 'status' => 'ko', 'message' => 'Accès refusé', 'debug' => 'Bad request' ) ) );
        $response->headers->set( 'Content-Type', 'application/json') ;
        return $response;
    }

    /**
     * @Route("/player/ajax/edit", name="team_player_ajax_edit")
     */
    public function ajaxEditAction( Request $request ) {
        if( $request->isXmlHttpRequest() ) {
            try {
                $user = $this->getUser();

                $em = $this->getDoctrine()->getManager();
                $player = $em->getRepository( 'TeamBundle:Player' )->findOneBy( array( 'id' => $request->get( 'id' ) ) );

                $inscription_end = \DateTime::createFromFormat( 'Y-m-d H:i:s', $em->getRepository( 'AdminBundle:Config' )->getOneBy( array( 'name' => 'inscription_end' ) ) );
                $class = ( new \DateTime() > $inscription_end ? $player->getClass() : $em->getRepository( 'TeamBundle:Classe' )->findOneBy( array( 'id' => $request->get( 'class' ) ) ) );

                if( $user->getTeam() == $player->getTeam() ) {
                    $team = $player->getTeam();
                    $remplacant = $player->getRemplacant() ? $em->getRepository( 'TeamBundle:Player' )->findOneBy( array( 'id' => $player->getRemplacant()->getId() ) ) : null;

                    if( $request->get( 'inverse' ) === 'true' ) {
                        $player->setRemplacant( null );
                        $player->setIsRemplacant( true );

                        $remplacant->setIsRemplacant( false );
                        $remplacant->setRemplacant( $player );

                        $tempPlayer = $player;

                        $player = $remplacant;
                        $remplacant = $tempPlayer;
                    } else {
                        $playerSearch = $em->getRepository('TeamBundle:Player')->findOneBy(array('pseudo' => $request->get('pseudo'), 'team' => null));

                        if ($request->get('newPlayer') == "true") {
                            $oldPlayer = $player;
                            $oldPlayer->setTeam(null);
                            $oldPlayer->setRemplacant(null);

                            if ($playerSearch and $playerSearch != $player)
                                $player = $playerSearch;
                            else {
                                $player = new Player();
                                $player->setPseudo($request->get('pseudo'));
                            }
                            $player->setLevel($request->get('level'));
                            $player->setClass($class);
                            $player->setIsRemplacant(false);

                            $player->setTeam($team);

                            $em->persist($player);
                        } else {
                            $player->setPseudo($request->get('pseudo'));
                            $player->setLevel($request->get('level'));
                            $player->setClass($class);
                        }

                        if (!empty($request->get('remplacantPseudo')) && !empty($request->get('remplacantLevel'))) {
                            $playerRemplacantSearch = $em->getRepository('TeamBundle:Player')->findOneBy(array('pseudo' => $request->get('remplacantPseudo'), 'team' => null));

                            if (!$remplacant || $request->get('newRemplacant') == "true") {
                                if ($remplacant) {
                                    $oldRemplacant = $remplacant;
                                    $oldRemplacant->setTeam(null);
                                }

                                if ($playerRemplacantSearch and $playerRemplacantSearch != $remplacant)
                                    $remplacant = $playerRemplacantSearch;
                                else {
                                    $remplacant = new Player();
                                    $remplacant->setPseudo($request->get('remplacantPseudo'));
                                }

                                $remplacant->setLevel($request->get('remplacantLevel'));
                                $remplacant->setClass($class);
                                $remplacant->setIsRemplacant(true);
                                $remplacant->setTeam($team);

                                $em->persist($remplacant);

                                $player->setRemplacant($remplacant);
                            } else {
                                $remplacant->setPseudo($request->get('remplacantPseudo'));
                                $remplacant->setLevel($request->get('remplacantLevel'));
                                $remplacant->setClass($class);
                                $remplacant->setTeam($team);
                                $remplacant->setIsRemplacant(true);
                            }
                        } else {
                            if ($remplacant) {
                                $player->setRemplacant(null);
                                $remplacant->setTeam(null);
                            }
                        }
                    }

                    $errors_validator = $this->get( 'validator' )->validate( $player );
                    // TODO : Détecter les problèmes de changements de joueur si c'est un joueur qui existe déjà (et donc qui est dans une équipe)
                    if( count( $errors_validator ) == 0 ) {
                        $em->getConnection()->beginTransaction();
                        $em->flush();
                        $errors_teamControl = $this->get( 'team.control_team' )->checkCompo( $team->getPlayers() );
                        if(count( $errors_teamControl ) == 0) {
                            $em->getConnection()->commit();
                            $response = new Response( json_encode( array( 'status' => 'ok', 'return' => $this->render('TeamBundle:Default:playerRow.html.twig', array( 'player' => $player, 'team' => $team ) )->getContent() ) ) );
                        } else {
                            $em->getConnection()->rollBack();
                            $response = new Response( json_encode( array( 'status' => 'ko', 'message' => 'Impossible de modifier le joueur', 'errors' => $this->render( 'TeamBundle:Default:validation.html.twig', array( 'errors_validator' => $errors_teamControl ) )->getContent(), 'debug' => '' ) ) );
                        }
                    } else
                        $response = new Response( json_encode( array( 'status' => 'ko', 'message' => 'Impossible de modifier le joueur', 'errors' => $this->render( 'TeamBundle:Default:validation.html.twig', array( 'errors_validator' => $errors_validator ) )->getContent(), 'debug' => '' ) ) );
                } else
                    $response = new Response( json_encode( array( 'status' => 'ko', 'message' => 'Vous n\'avez pas la permission d\'éditer ce joueur', 'debug' => 'Utilisateur connecté != manager de l\'équipe du joueur' ) ) );
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
}
