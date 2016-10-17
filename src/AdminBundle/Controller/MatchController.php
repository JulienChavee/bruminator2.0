<?php

namespace AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MatchController extends Controller
{
    /**
     * @Route("/match", name="admin_match")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $nbTeam = count($em->getRepository('TeamBundle:Team')->findAll());
        $nbTeamValid = count($em->getRepository('TeamBundle:Team')->findBy(array('valid' => 1)));
        $matchs = $em->getRepository( 'MatchBundle:Matchs' )->findByDate( new \DateTime() );

        return $this->render( 'AdminBundle:Match:index.html.twig', array( 'matchs' => $matchs, 'nbTeam' => $nbTeam, 'nbTeamValid' => $nbTeamValid ) );
    }

    /**
     * @Route("/match/ajax/generate", name="admin_match_ajax_generate")
     */
    public function ajaxGenerateMatchAction( Request $request ) {
        // TODO : Vérifier ajax et si la fin des inscription est < now()
        $response = new Response( json_encode( $this->get( 'match.control_match' )->generateMatch() ) );
        $response->headers->set( 'Content-Type', 'application/json' );

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
}
