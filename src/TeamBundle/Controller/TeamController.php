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
            $matchs = $em->getRepository( 'MatchBundle:Matchs' )->findByTeam( $team->getId(), true );

            return $this->render( 'TeamBundle:Default:index.html.twig', array( 'team' => $team, 'matchs' => $matchs, 'classes' => $classes ) );
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
                        $team->setRegistered( true );
                        $players = json_decode( $request->get( 'players' ), true );

                        foreach( $players as $k => $v ) {
                            if( $em->getRepository( 'TeamBundle:Player' )->findOneBy( array( 'pseudo' => $v[ 'name' ] ) ) )
                                $player = $em->getRepository( 'TeamBundle:Player' )->findOneBy( array( 'pseudo' => $v[ 'name' ] ) );
                            else
                                $player = new Player();

                            $class = $em->getRepository( 'TeamBundle:Classe' )->findOneBy( array( 'id' => $v[ 'class' ] ) );

                            $player->setPseudo( $v[ 'name' ] );
                            $player->setLevel( $v[ 'level' ] );
                            $player->setClass( $class );
                            $player->setIsRemplacant( false );
                            $player->setTeam( $team );
                            $player->setRemplacant( null );

                            $em->persist( $player );

                            if ( !empty( $v[ 'remplacant' ][ 'name' ] ) && !empty( $v[ 'remplacant' ][ 'level' ] ) ) {
                                if( $em->getRepository( 'TeamBundle:Player' )->findOneBy( array( 'pseudo' => $v[ 'name' ] ) ) )
                                    $remplacant = $em->getRepository( 'TeamBundle:Player' )->findOneBy( array( 'pseudo' => $v[ 'name' ] ) );
                                else
                                    $remplacant = new Player();

                                $remplacant->setPseudo( $v[ 'remplacant' ][ 'name' ] );
                                $remplacant->setLevel( $v[ 'remplacant' ][ 'level' ]);
                                $remplacant->setClass( $class );
                                $remplacant->setIsRemplacant( true );
                                $remplacant->setTeam( $team );
                                $player->setRemplacant( $remplacant );

                                $em->persist( $remplacant );
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
     * @Route("/ajax/search", name="team_ajax_search")
     */
    public function ajaxSearch( Request $request ) {
        if( $request->isXmlHttpRequest() ) {
            try {
                $em = $this->getDoctrine()->getManager();

                $terms = preg_replace( '/^(.*)+$/', '%$0%', explode( ' ', $request->get( 'search' ) ) );

                $teams = $em->getRepository( 'TeamBundle:Team' )->search( $terms );

                $response = new Response( json_encode( array( 'status' => 'ok', 'return' => $this->render( 'TeamBundle:Front:teamsGrid.html.twig', array( 'teams' => $teams ) )->getContent() ) ) );
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
     * @Route("/view/liste/{page}", name="team_front_homepage", defaults={"page": 1})
     */
    public function frontIndexAction( $page ) {
        $em = $this->getDoctrine()->getManager();

        $maxTeams = 9;
        $teams = $em->getRepository( 'TeamBundle:Team' )->getList( $page, $maxTeams );
        $pagination = array(
            'page' => $page,
            'route' => 'team_front_homepage',
            'pages_count' => ceil( $teams->count() / $maxTeams ),
            'route_params' => array()
        );


        return $this->render( 'TeamBundle:Front:index.html.twig', array( 'teams' => $teams, 'pagination' => $pagination ) );
    }

    /**
     * @Route("/view/{id}-{slugTeam}", name="team_front_team_view", defaults={"id": "", "slugTeam": ""})
     */
    public function frontTeamViewAction( $id, $slugTeam ) {
        $em = $this->getDoctrine()->getManager();
        $team = $em->getRepository( 'TeamBundle:Team' )->findOneBy( array( 'id' => $id ) );

        if( $this->get( 'cocur_slugify' )->slugify( $team->getName() ) == $slugTeam )
            return $this->render( 'TeamBundle:Front:team.html.twig', array( 'team' => $team ) );
        else
            return $this->redirectToRoute( 'team_front_team_view', array( 'id' => $team->getId(), 'slugTeam' => $this->get( 'cocur_slugify' )->slugify( $team->getName() ) ) );
    }

    public function frontViewTeamMatchsAction( $id ) {
        $em = $this->getDoctrine()->getManager();
        $team = $em->getRepository( 'TeamBundle:Team' )->findOneBy( array( 'id' => $id ) );
        $matchs = $em->getRepository( 'MatchBundle:Matchs' )->findByTeam( $id, true );

        return $this->render( 'TeamBundle:Front:matchs.html.twig', array( 'matchs' => $matchs, 'team' => $team ) );
    }

    /**
     * @Route("/synergie", name="team_front_synergie")
     */
    public function frontSynergieAction() {
        $em = $this->getDoctrine()->getManager();

        $classes = $em->getRepository( 'TeamBundle:Classe' )->findAll();

        return $this->render( 'TeamBundle:Front:synergie.html.twig', array( 'classes' => $classes ) );
    }

    /**
     * @Route("/synergie/ajax/getSynergie", name="team_front_synergie_ajax_getsynergie")
     */
    public function frontSynergieAjaxGetSynergie( Request $request ) {
        if( $request->isXmlHttpRequest() ) {
            try {
                $em = $this->getDoctrine()->getManager();

                $synergie = $em->getRepository( 'TeamBundle:SynergieClass' )->getSynergie( $request->get( 'class1' ), $request->get( 'class2' ) );

                $response = new Response( json_encode( array( 'status' => 'ok', 'return' => $synergie ) ) );
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
     * @Route("/synergie/ajax/getClassPoints", name="team_front_synergie_ajax_getclasspoints")
     */
    public function frontSynergieAjaxGetClassPoints( Request $request ) {
        if( $request->isXmlHttpRequest() ) {
            try {
                $em = $this->getDoctrine()->getManager();

                $class = $em->getRepository( 'TeamBundle:Classe' )->findOneBy( array( 'id' => $request->get( 'class' ) ) );

                $response = new Response( json_encode( array( 'status' => 'ok', 'return' => $class->getPoints() ) ) );
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
     * @Route("/synergie/ajax/getClass4", name="team_front_synergie_ajax_getclass4")
     */
    public function frontSynergieAjaxGetClass4( Request $request ) {
        if( $request->isXmlHttpRequest() ) {
            try {
                $em = $this->getDoctrine()->getManager();

                $class4 = $em->getRepository( 'TeamBundle:SynergieClass' )->getClass4( $request->get( 'class1' ), $request->get( 'class2' ), $request->get( 'class3' ) );

                $response = new Response( json_encode( array( 'status' => 'ok', 'return' => $class4 ) ) );
            }
            catch( \Exception $e ) {
                $response = new Response( json_encode( array( 'status' => 'ko', 'message' => 'Une erreur inconnue s\'est produite', 'debug' => $e->getTraceAsString() ) ) );
            }
            $response->headers->set( 'Content-Type', 'application/json' );
            return $response;
        }

        $response = new Response( json_encode( array( 'status' => 'ko', 'message' => 'Accès non autorisé', 'debug' => 'Bad request' ) ) );
        $response->headers->set( 'Content-Type', 'application/json') ;
        return $response;
    }

    private function validateTeam( $teamName, $players, $dispo ) {
        $em = $this->getDoctrine()->getManager();

        $errors = array();
        $pilliers = 0;
        $bannedClass = 0;
        $levelTotal = 0;
        $classes = array();
        $synergieTotale = 0;

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

                foreach( $players as $k2 => $v2 ) {
                    if( $k < $k2 )
                        $synergieTotale += $em->getRepository( 'TeamBundle:SynergieClass' )->getSynergie( $class->getId(), $v2['class'] );
                }

                $synergieTotale += $class->getPoints();

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

        // TODO : Meilleure gestion des contraintes liées au type de tournoi
        /*if( $pilliers > 1 )
            $errors[] = "Vous ne pouvez pas avoir plus d'une classe pillier";

        if( $bannedClass > 0 )
            $errors[] = "Vous avez des classes ne pouvant pas être jouées ensemble";*/

        if( $synergieTotale > $em->getRepository( 'AdminBundle:Config' )->getOneBy( array( 'name' => 'synergie_max' ) ) )
            $errors[] = "Votre composition a une synergie trop forte ($synergieTotale)";

        if( empty( $dispo ) )
            $errors[] = "Vous devez indiquer vos disponibilités";

        if( $levelTotal / $em->getRepository( 'AdminBundle:Config' )->getOneBy( array( 'name' => 'nb_players_team' ) ) < $em->getRepository( 'AdminBundle:Config' )->getOneBy( array( 'name' => 'min_average_level' ) ) )
            $errors[] = "Le niveau moyen de l'équie doit être supérieur à ".$em->getRepository( 'AdminBundle:Config' )->getOneBy( array( 'name' => 'min_average_level' ) );

        return $errors;
    }
}
