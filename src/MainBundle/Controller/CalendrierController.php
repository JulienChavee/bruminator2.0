<?php

namespace MainBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CalendrierController extends Controller
{
    /**
     * @Route("/calendrier", name="calendrier")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $timeNow = new \DateTime();
        $time = (new \DateTime())->setTimestamp( mktime( 0, 0, 0, date( "m" ), 1, date( "Y" ) ) );
        $matchs = $em->getRepository( 'MatchBundle:Matchs' )->findByDate( $time, array( 'field' => 'date', 'type' => 'ASC') );
        return $this->render( 'MainBundle:Calendrier:index.html.twig', array( 'time' => $time, 'matchs' => $matchs, 'timeNow' => $timeNow ) );
    }

    /**
     * @Route("/arbre", name="arbre")
     */
    public function arbreAction() {
        $em = $this->getDoctrine()->getManager();

        $matchs = $em->getRepository( 'MatchBundle:Matchs' )->findMatchsPhasesFinales();

        return $this->render( 'MainBundle:Calendrier:arbre.html.twig', array( 'matchs' => $matchs ) );
    }

    /**
     * @Route("/calendrier/ajax/get", name="calendrier_ajax_get")
     */
    public function ajaxGetCalendar( Request $request ) {
        if( $request->isXmlHttpRequest() ) {
            try {
                $em = $this->getDoctrine()->getManager();

                $time = (new \DateTime())->setTimestamp( mktime( 0, 0, 0, $request->get( 'month' ), 1, $request->get( 'year' ) ) );
                $timeNow = new \DateTime();
                $matchs = $em->getRepository( 'MatchBundle:Matchs' )->findByDate( $time, array( 'field' => 'date', 'type' => 'ASC') );

                $response = new Response( json_encode( array( 'status' => 'ok', 'return' => $this->render( 'MainBundle:Calendrier:calendarGrid.html.twig', array( 'matchs' => $matchs, 'time' => $time, 'timeNow' => $timeNow ) )->getContent() ) ) );
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
