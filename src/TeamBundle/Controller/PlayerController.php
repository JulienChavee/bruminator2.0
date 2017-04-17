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

                $inscription_end = \DateTime::createFromFormat( 'Y-m-d H:i:s', $em->getRepository( 'MainBundle:Edition' )->getDate( 'inscription', 'end' ) );
                $class = ( new \DateTime() > $inscription_end ? $player->getClass() : $em->getRepository( 'TeamBundle:Classe' )->findOneBy( array( 'id' => $request->get( 'class' ) ) ) );

                $team = $player->getTeam();

                if( $player->getClass() != $class )
                    $team->setValid(false);

                if( $user->getTeam() == $player->getTeam() ) {
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
                        $em->flush();
                        $errors_teamControl = $this->render( 'TeamBundle:Default:teamErrors.html.twig', array( 'errors' => $this->get( 'team.control_team' )->checkCompo( $team->getPlayers() ) ) )->getContent();
                        $response = new Response( json_encode( array( 'status' => 'ok', 'return' => $this->render('TeamBundle:Default:playerRow.html.twig', array( 'player' => $player, 'team' => $team ) )->getContent(), 'errors' => $errors_teamControl ) ) );
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

    /**
     * @Route("/player/ajax/add", name="team_player_ajax_add")
     */
    public function ajaxAddAction( Request $request ) {
        if( $request->isXmlHttpRequest() ) {
            try {
                $user = $this->getUser();

                $em = $this->getDoctrine()->getManager();

                $inscription_end = \DateTime::createFromFormat( 'Y-m-d H:i:s', $em->getRepository( 'MainBundle:Edition' )->getDate( 'inscription', 'end' ) );
                $class = $em->getRepository( 'TeamBundle:Classe' )->findOneBy( array( 'id' => $request->get( 'class' ) ) );

                if( $user->getTeam() && new \DateTime() < $inscription_end ) {
                    $player = $em->getRepository( 'TeamBundle:Player' )->findOneBy( array( 'pseudo' => $request->get( 'pseudo') ) );

                    if( is_null( $player ) )
                        $player = new Player();

                    $player->setPseudo( $request->get( 'pseudo' ) );
                    $player->setLevel( $request->get( 'level' ) );
                    $player->setClass( $class );
                    $player->setIsRemplacant( false );
                    $player->setTeam( $user->getTeam() );

                    if( !empty( $request->get( 'remplacantPseudo' ) ) && !empty( $request->get( 'remplacantLevel' ) ) ) {
                        $remplacant = $em->getRepository( 'TeamBundle:Player' )->findOneBy( array( 'pseudo' => $request->get( 'remplacantPseudo') ) );

                        if( is_null( $remplacant ) )
                            $remplacant = new Player();

                        $remplacant->setPseudo( $request->get( 'remplacantPseudo' ) );
                        $remplacant->setLevel( $request->get( 'remplacantLevel' ) );
                        $remplacant->setClass( $class );
                        $remplacant->setIsRemplacant( true );
                        $remplacant->setTeam( $user->getTeam() );
                        $remplacant->setRemplacant( null );
                        $player->setRemplacant( $remplacant );

                        $em->persist($remplacant);
                    }

                    $em->persist($player);

                    $errors_validator = $this->get( 'validator' )->validate( $player );
                    // TODO : Détecter les problèmes de changements de joueur si c'est un joueur qui existe déjà (et donc qui est dans une équipe)
                    if( count( $errors_validator ) == 0 ) {
                        $em->flush();
                        $errors_teamControl = $this->render( 'TeamBundle:Default:teamErrors.html.twig', array( 'errors' => $this->get( 'team.control_team' )->checkCompo( $user->getTeam()->getPlayers() ) ) )->getContent();
                        $response = new Response( json_encode( array( 'status' => 'ok', 'return' => $this->render('TeamBundle:Default:playerRow.html.twig', array( 'player' => $player, 'team' => $user->getTeam() ) )->getContent(), 'errors' => $errors_teamControl ) ) );
                    } else
                        $response = new Response( json_encode( array( 'status' => 'ko', 'message' => 'Impossible de modifier le joueur', 'errors' => $this->render( 'TeamBundle:Default:validation.html.twig', array( 'errors_validator' => $errors_validator ) )->getContent(), 'debug' => '' ) ) );
                } else
                    $response = new Response( json_encode( array( 'status' => 'ko', 'message' => 'Vous n\'avez pas la permission d\'ajouter ce joueur', 'debug' => 'Utilisateur connecté n\'a pas d\'équipe ou inscription terminée' ) ) );
            }
            catch( \Exception $e ) {
                $response = new Response( json_encode( array( 'status' => 'ko', 'message' => 'Une erreur inconnue s\'est produite', 'debug' => $e->__toString() ) ) );
            }
            $response->headers->set( 'Content-Type', 'application/json' );
            return $response;
        }
        $response = new Response( json_encode( array( 'status' => 'ko', 'message' => 'Accès refusé', 'debug' => 'Bad request' ) ) );
        $response->headers->set( 'Content-Type', 'application/json') ;
        return $response;
    }

    /**
     * @Route("/player/ajax/remove", name="team_player_ajax_remove")
     */
    public function ajaxRemoveAction( Request $request ) {
        if( $request->isXmlHttpRequest() ) {
            try {
                $user = $this->getUser();

                $em = $this->getDoctrine()->getManager();

                $inscription_end = \DateTime::createFromFormat( 'Y-m-d H:i:s', $em->getRepository( 'MainBundle:Edition' )->getDate( 'inscription', 'end' ) );

                if( $user->getTeam() && new \DateTime() < $inscription_end ) {

                    $player = $em->getRepository( 'TeamBundle:Player' )->findOneBy( array( 'id' => $request->get( 'id' ), 'team' => $user->getTeam() ) );

                    if( $player ) {
                        $remplacant = !is_null( $player->getRemplacant() ) ? $em->getRepository( 'TeamBundle:Player' )->findOneBy( array( 'id' => $player->getRemplacant() ) ) : null;

                        $player->setTeam( null );

                        if( $remplacant )
                            $remplacant->setTeam( null );

                        $em->flush();

                        $errors_teamControl = $this->render( 'TeamBundle:Default:teamErrors.html.twig', array( 'errors' => $this->get( 'team.control_team' )->checkCompo( $user->getTeam()->getPlayers() ) ) )->getContent();
                        $response = new Response( json_encode( array( 'status' => 'ok', 'return' => $this->render('TeamBundle:Default:playerRow.html.twig', array( 'player' => $player, 'team' => $user->getTeam() ) )->getContent(), 'errors' => $errors_teamControl ) ) );
                    } else
                        $response = new Response( json_encode( array( 'status' => 'ko', 'message' => 'Impossible de retirer le joueur', 'debug' => 'Joueur inexistant ou pas dans la team gérée par ce manager' ) ) );
                } else
                    $response = new Response( json_encode( array( 'status' => 'ko', 'message' => 'Vous n\'avez pas la permission de supprimer ce joueur', 'debug' => 'Utilisateur connecté n\'a pas d\'équipe ou inscription terminée' ) ) );
            }
            catch( \Exception $e ) {
                $response = new Response( json_encode( array( 'status' => 'ko', 'message' => 'Une erreur inconnue s\'est produite', 'debug' => $e->__toString() ) ) );
            }
            $response->headers->set( 'Content-Type', 'application/json' );
            return $response;
        }
        $response = new Response( json_encode( array( 'status' => 'ko', 'message' => 'Accès refusé', 'debug' => 'Bad request' ) ) );
        $response->headers->set( 'Content-Type', 'application/json') ;
        return $response;
    }
}
