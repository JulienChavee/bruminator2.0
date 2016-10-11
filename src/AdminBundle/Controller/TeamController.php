<?php

namespace AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class TeamController extends Controller
{
    /**
     * @Route("/team", name="admin_team")
     */
    public function indexAction() {
        $em = $this->getDoctrine()->getManager();
        $array_teams = $em->getRepository( 'TeamBundle:Team')->findAll();

        return $this->render( 'AdminBundle:Team:index.html.twig', array( 'array_teams' => $array_teams ) );
    }

    /**
     * @Route("/team/ajax/validate", name="admin_team_ajax_validate")
     */
    public function ajaxValidateAction( Request $request ) {
        if( $request->isXmlHttpRequest() ) {
            try {
                $em = $this->getDoctrine()->getManager();
                $team = $em->getRepository( 'TeamBundle:Team' )->findOneBy( array( 'id' => $request->get( 'id' ) ) );

                if( $team ) {
                    $team->setValid( !$team->getValid() );
                    $em->flush();
                    $this->get( 'log.action_log')->Log( $this->getUser(), '{"value":"Modification de l\'état de validation de l\'équipe (de %s à %s)","parameters":{"'.var_export( !$team->getValid(), true ).'","'.var_export( $team->getPaid(), true ).'"', $_SERVER[ 'REMOTE_ADDR' ], get_class( $team ), $team );
                    $response = new Response( json_encode( array( 'status' => 'ok', 'return' => $this->render( 'AdminBundle:Team:teamRow.html.twig', array( 'team' => $team ) )->getContent() ) ) );
                } else
                    $response = new Response( json_encode( array( 'status' => 'ko', 'message' => 'La team n\'existe pas', 'debug' => 'La team n\'existe pas' ) ) );
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
     * @Route("/team/ajax/pay", name="admin_team_ajax_pay")
     */
    public function ajaxPayAction( Request $request ) {
        if( $request->isXmlHttpRequest() ) {
            try {
                $em = $this->getDoctrine()->getManager();
                $team = $em->getRepository( 'TeamBundle:Team' )->findOneBy( array( 'id' => $request->get( 'id' ) ) );

                if( $team ) {
                    $team->setPaid( !$team->getPaid() );
                    $em->flush();
                    $this->get( 'log.action_log')->Log( $this->getUser(), '{"value":"Modification de l\'état du paiement de l\'équipe (de %s à %s)","parameters":{"'.var_export( !$team->getPaid(), true ).'","'.var_export( $team->getPaid(), true ).'"', $_SERVER[ 'REMOTE_ADDR' ], get_class( $team ), $team );
                    $response = new Response( json_encode( array( 'status' => 'ok', 'return' => $this->render( 'AdminBundle:Team:teamRow.html.twig', array( 'team' => $team ) )->getContent() ) ) );
                } else
                    $response = new Response( json_encode( array( 'status' => 'ko', 'message' => 'La team n\'existe pas', 'debug' => 'La team n\'existe pas' ) ) );
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
}
