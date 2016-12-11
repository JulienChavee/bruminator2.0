<?php

namespace AdminBundle\Controller;

use MainBundle\Entity\Edition;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class EditionController extends Controller
{
    /**
     * @Route("/edition", name="admin_edition")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $array_edition = $em->getRepository( 'MainBundle:Edition' )->findAll();

        return $this->render( 'AdminBundle:Edition:index.html.twig', array( 'array_editions' => $array_edition ) );
    }

    /**
     * @Route("/edition/ajax/add", name="admin_edition_ajax_add")
     */
    public function ajaxAddAction( Request $request ) {
        if( $request->isXmlHttpRequest() ) {
            try {
                $em = $this->getDoctrine()->getManager();

                $edition = new Edition();
                $edition->setName( $request->get( 'name' ) );
                $edition->setData( array( 'date' => json_decode( $request->get( 'date' ) ) ) );

                $em->persist( $edition );
                $em->flush();
                $response = new Response( json_encode( array( 'status' => 'ok', 'return' => $this->render( 'AdminBundle:Edition:editionRow.html.twig', array( 'edition' => $edition ) )->getContent() ) ) );
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
