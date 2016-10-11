<?php

namespace TeamBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use TeamBundle\Entity\Player;
use TeamBundle\Entity\Team;
use \DateTime;

class TeamController extends Controller
{
    /**
     * @Route("/", name="team_homepage")
     */
    public function indexAction()
    {
        $user = $this->getUser();
        $team = $user->getTeam();

        if( $team ) {
            $em = $this->getDoctrine()->getManager();
            $classes = $em->getRepository( 'TeamBundle:Classe' )->findBy( array(), array( 'name' => 'ASC' ) );

            return $this->render( 'TeamBundle:Default:index.html.twig', array( 'team' => $team, 'classes' => $classes ) );
        } else {// Si aucune équipe inscrite, on redirige sur la page pour en inscrire une
            $this->addFlash( 'danger', 'Vous ne possédez aucune équipe' );

            return $this->redirectToRoute( 'team_registration' );
        }
    }

    /**
     * @Route("/registration", name="team_registration")
     */
    public function registrationAction( Request $request ) {
        $em = $this->getDoctrine()->getManager();

        $now = new DateTime( 'now' );
        $inscription_end = DateTime::createFromFormat( 'Y-m-d H:i:s', $em->getRepository( 'AdminBundle:Config' )->getOneBy( array( 'name' => 'inscription_end' ) ) );

        if( $now < $inscription_end ) {
            $user = $this->getUser();

            if ( !empty( $user ) && empty( $user->getTeam() ) ) {
                $classes = $em->getRepository( 'TeamBundle:Classe' )->findBy( array(), array( 'name' => 'ASC' ) );

                return $this->render( 'TeamBundle:Default:registration.html.twig', array( 'classes' => $classes ) );
            } else {
                $this->addFlash( 'danger', 'Vous possédez déjà une équipe' );

                return $this->redirectToRoute( 'team_homepage' );
            }
        } else {
            $this->addFlash( 'danger', 'Les inscriptions d\'équipes sont terminées' );

            return $this->redirectToRoute( 'homepage' );
        }
    }

    /**
     * @Route("/ajax/registration", name="team_ajax_registration")
     */
    public function ajaxRegistrationAction( Request $request ) {
        if( $request->isXmlHttpRequest() ) {
            $user = $this->getUser();

            if( !empty( $user ) && empty( $user->getTeam() ) ) {
                $errors = $this->validateTeam( $request->get( 'name' ), $request->get( 'players'), $request->get( 'dispo' ) );

                if( empty( $errors ) ) {
                    try {
                        $em = $this->getDoctrine()->getManager();

                        $team = new Team();

                        $team->setName( $request->get( 'name' ) );
                        $team->setInscriptionDate( new \DateTime() );
                        $team->setPaid( false );
                        $team->setValid( false );
                        $team->setAvailable( $request->get( 'dispo' ) );
                        $players = json_decode( $request->get( 'players' ), true );

                        foreach( $players as $k => $v ) {
                            $player = new Player();

                            $class = $em->getRepository( 'TeamBundle:Classe' )->findOneBy( array( 'id' => $v[ 'class' ] ) );

                            $player->setPseudo( $v[ 'name' ] );
                            $player->setLevel( $v[ 'level' ] );
                            $player->setClass( $class );
                            $player->setIsRemplacant( false );
                            $player->setTeam( $team );

                            $em->persist( $player );

                            if ( !empty( $v[ 'remplacant' ][ 'name' ] ) && !empty( $v[ 'remplacant' ][ 'level' ] ) ) {
                                $player = new Player();

                                $player->setPseudo( $v[ 'remplacant' ][ 'name' ] );
                                $player->setLevel( $v[ 'remplacant' ][ 'level' ]);
                                $player->setClass( $class );
                                $player->setIsRemplacant( true );
                                $player->setTeam( $team );

                                $em->persist( $player );
                            }
                        }

                        $user->setTeam( $team ); // On set la team de l'utilisateur si tout est ok

                        $em->persist( $team );
                        $em->flush();

                        $response = new Response( json_encode( array( 'status' => 'ok' ) ) );

                        $this->addFlash( 'success', 'Votre équipe a bien été inscrite !' );
                    } catch( \Exception $e ) {
                        $response = new Response( json_encode( array( 'status' => 'ko', 'message' => 'Une erreur inconnue s\'est produite', 'debug' => $e->getMessage() ) ) );
                    }
                } else {
                    $response = new Response( json_encode( array( 'status' => 'ko', 'message' => $errors ) ) );
                }
            } else {
                $response = new Response( json_encode( array( 'status' => 'ko', 'message' => 'Vous possédez déjà une équipe ou vous n\'êtes pas connecté' ) ) );
            }
            $response->headers->set( 'Content-Type', 'application/json' ) ;

            return $response;
        }

        $response = new Response( json_encode( array( 'status' => 'ko', 'message' => 'Accès non autorisé', 'debug' => 'Bad request' ) ) );
        $response->headers->set( 'Content-Type', 'application/json' ) ;

        return $response;
    }

    /**
     * @Route("/ajax/updatedispo", name="team_ajax_update_dispo")
     */
    public function ajaxUpdateDispo( Request $request ) {
        if( $request->isXmlHttpRequest() ) {
            try {
                $user = $this->getUser();

                $em = $this->getDoctrine()->getManager();
                $team = $em->getRepository( 'TeamBundle:Team' )->findOneBy( array( 'id' => $request->get( 'id' ) ) );

                if( $user->getTeam() == $team ) {
                    $team->setAvailable( $request->get( 'dispo' ) );

                    $errors = $this->get( 'validator' )->validate( $team );
                    if( count( $errors ) == 0 ) {
                        $em->flush();
                        $response = new Response( json_encode( array( 'status' => 'ok', 'return' => $this->render('TeamBundle:Default:dispoRow.html.twig', array( 'team' => $team ) )->getContent() ) ) );
                    } else
                        $response = new Response( json_encode( array( 'status' => 'ko', 'message' => 'Impossible de modifier les disponibilités'/*, 'errors' => $this->render( 'TeamBundle:Default:validation.html.twig', array( 'errors_validator' => $errors_validator, 'errors_teamControl' => $errors_teamControl ) )->getContent(), 'debug' => ''*/ ) ) ); // TODO : Renvoyer les erreurs
                } else
                    $response = new Response( json_encode( array( 'status' => 'ko', 'message' => 'Vous n\'avez pas la permission d\'éditer cette équipe', 'debug' => 'Utilisateur connecté != manager de l\'équipe' ) ) );
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
     * @Route("/ajax/getdispo", name="team_ajax_get_dispo")
     */
    public function ajaxGetDispo( Request $request ) {
        if( $request->isXmlHttpRequest() ) {
            try {
                $user = $this->getUser();

                $em = $this->getDoctrine()->getManager();
                $team = $em->getRepository( 'TeamBundle:Team' )->findOneBy( array( 'id' => $request->get( 'id' ) ) );

                if( $user->getTeam() == $team ) {
                    $response = new Response( json_encode( array( 'status' => 'ok', 'return' => $team->getAvailable() ) ) );
                } else
                    $response = new Response( json_encode( array( 'status' => 'ko', 'message' => 'Vous n\'avez pas la permission d\'éditer cette équipe', 'debug' => 'Utilisateur connecté != manager de l\'équipe' ) ) );
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
     * @Route("/view/{id}-{slugTeam}", name="team_front_homepage", defaults={"id": "", "slugTeam": ""},)
     */
    public function frontIndexAction( $id, $slugTeam ) {
        if( empty( $id ) || empty( $slugTeam ) ) {
            $em = $this->getDoctrine()->getManager();
            $teams = $em->getRepository( 'TeamBundle:Team' )->findAll();
            return $this->render( 'TeamBundle:Front:index.html.twig', array( 'teams' => $teams ) );
        } else
            return $this->frontViewTeamAction( $id, $slugTeam );
    }

    public function frontViewTeamAction( $id, $slugTeam ) {
        $em = $this->getDoctrine()->getManager();
        $team = $em->getRepository( 'TeamBundle:Team' )->findOneBy( array( 'id' => $id ) );
        if( $this->get( 'cocur_slugify' )->slugify( $team->getName() ) == $slugTeam )
            return $this->render( 'TeamBundle:Front:team.html.twig', array( 'team' => $team ) );
        else
            return $this->redirectToRoute( 'team_front_homepage', array( 'id' => $team->getId(), 'slugTeam' => $this->get( 'cocur_slugify' )->slugify( $team->getName() ) ) );
    }

    private function validateTeam( $teamName, $players, $dispo ) {
        $em = $this->getDoctrine()->getManager();

        $errors = array();
        $pilliers = 0;
        $bannedClass = 0;
        $levelTotal = 0;
        $classes = array();

        if( empty( $teamName ) )
            $errors[] = "Le nom de l'équipe ne peut pas être vide";

        $players = json_decode( $players, true );

        foreach( $players as $k => $v ) {
            if( empty( $v[ 'name' ] ) )
                $errors[] = "Le pseudo du joueur $k ne peut pas être vide";

            if( empty( $v[ 'level' ] ) && !is_numeric( $v[ 'level' ] ) )
                $errors[] = "Le niveau du joueur $k ne peut pas être vide";
            else if( $v[ 'level' ] < $em->getRepository( 'AdminBundle:Config' )->getOneBy( array( 'name' => 'min_level' ) ) )
                $errors[] = "Le niveau du joueur $k ne peut pas être inférieur à ".$em->getRepository( 'AdminBundle:Config' )->getOneBy( array( 'name' => 'min_level' ) );
            else if ( $v[ 'level' ] > $em->getRepository( 'AdminBundle:Config' )->getOneBy( array( 'name' => 'max_level' ) ) ) {
                $errors[] = "Le niveau du joueur $k ne peut pas être supérieur à ".$em->getRepository( 'AdminBundle:Config' )->getOneBy( array( 'name' => 'max_level' ) );
            }

            $levelTotal += $v[ 'level' ];

            if( empty( $v[ 'class' ] ) )
                $errors[] = "La classe du joueur $k ne peut pas être vide";
            else {
                $class = $em->getRepository( 'TeamBundle:Classe' )->findOneBy( array( 'id' => $v[ 'class' ] ) );
                $classes[] = $class;

                if( $class && $class->getIsPillier() )
                    $pilliers++;

                $tempClassBanned = json_decode( $class->getBannedClass() );

                if( !is_null( $tempClassBanned ) ) {
                    foreach ($tempClassBanned as $k2 => $v2) {
                        $temp = $em->getRepository('TeamBundle:Classe')->findOneBy(array('id' => $v2));

                        if (in_array($temp, $classes))
                            $bannedClass++;
                    }
                }
            }
        }

        if( count( array_unique( $classes, SORT_REGULAR ) ) < count( $classes ) )
            $errors[] = "Vous ne pouvez pas avoir de classe doublon";

        if( $pilliers > 1 )
            $errors[] = "Vous ne pouvez pas avoir plus d'une classe pillier";

        if( $bannedClass > 0 )
            $errors[] = "Vous avez des classes ne pouvant pas être jouées ensemble";

        if( empty( $dispo ) )
            $errors[] = "Vous devez indiquer vos disponibilités";

        if( $levelTotal / $em->getRepository( 'AdminBundle:Config' )->getOneBy( array( 'name' => 'nb_players_team' ) ) < $em->getRepository( 'AdminBundle:Config' )->getOneBy( array( 'name' => 'min_average_level' ) ) )
            $errors[] = "Le niveau moyen de l'équie doit être supérieur à ".$em->getRepository( 'AdminBundle:Config' )->getOneBy( array( 'name' => 'min_average_level' ) );

        return $errors;
    }
}
