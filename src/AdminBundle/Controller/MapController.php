<?php

namespace AdminBundle\Controller;

use MatchBundle\Entity\MapDate;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MapController extends Controller
{
    /**
     * @Route("/map", name="admin_map")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $maps = $em->getRepository( 'MatchBundle:Map' )->findAll();
        $mapDates = $em->getRepository( 'MatchBundle:MapDate' )->findBy( array ( 'edition' => $em->getRepository( 'MainBundle:Edition' )->findLastEdition() ) );

        return $this->render( 'AdminBundle:Map:index.html.twig', array( 'maps' => $maps, 'mapDates' => $mapDates, 'mapMatchs' => array() ) );
    }

    /**
     * @Route("/map/ajax/hide", name="admin_map_ajax_hide")
     */
    public function ajaxHideAction( Request $request )
    {
        if( $request->isXmlHttpRequest() )
        {
            try
            {
                $em = $this->getDoctrine()->getManager();
                $map = $em->getRepository( 'MatchBundle:Map' )->findOneBy( array( 'id' => $request->get( 'id' ) ) );

                $map->setVisible( !$map->getVisible() );
                $em->flush();

                $response = new Response( json_encode( array( 'status' => 'ok', 'return' => $this->render( 'AdminBundle:Map:mapRow.html.twig', array( 'map' => $map ) )->getContent() ) ) );
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
     * @Route("/map/ajax/showAddMapDate", name="admin_map_ajax_show_add_map_date")
     */
    public function ajaxShowAddMapDateAction( Request $request )
    {
        if( $request->isXmlHttpRequest() )
        {
            try
            {
                $em = $this->getDoctrine()->getManager();

                $edition = $em->getRepository( 'MainBundle:Edition' )->findLastEdition();

                $matchs = $em->getRepository( 'MatchBundle:Matchs' )->findMatchWithoutResult( $edition );
                $mapDates = $em->getRepository( 'MatchBundle:MapDate' )->findBy( array( 'edition' => $edition ) );
                $maps = $em->getRepository( 'MatchBundle:Map' )->findAll();

                $response = new Response( json_encode( array( 'status' => 'ok', 'return' => $this->render( 'AdminBundle:Map:modalAddMapDate.html.twig', array( 'matchs' => $matchs, 'maps' => $maps, 'mapDates' => $mapDates ) )->getContent() ) ) );
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
     * @Route("/map/ajax/addMapDate", name="admin_map_ajax_add_map_date")
     */
    public function ajaxAddMapDateAction( Request $request )
    {
        if( $request->isXmlHttpRequest() )
        {
            try
            {
                $em = $this->getDoctrine()->getManager();

                $map = $em->getRepository( 'MatchBundle:Map' )->findOneBy( array( 'id' => $request->get( 'map' ) ) );

                if( !empty( $request->get( 'match' ) ) )
                {
                    $data = $em->getRepository( 'MatchBundle:Matchs' )->findOneBy( array( 'id' => $request->get( 'match' ) ) );

                    $data->setMap( $map );
                }
                else
                {
                    $data = new MapDate();

                    $data->setMap( $map );
                    $data->setDate( \DateTime::createFromFormat( 'Y-m-d',$request->get( 'date' ) ) );
                    $data->setEdition( $em->getRepository( 'MainBundle:Edition')->findLastEdition() );

                    $em->persist( $data );
                }

                $em->flush();

                $response = new Response( json_encode( array( 'status' => 'ok', 'return' => $this->render( 'AdminBundle:Map:mapDateRow.html.twig', array( 'data' => $data ) )->getContent() ) ) );
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
