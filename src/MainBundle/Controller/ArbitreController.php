<?php

namespace MainBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ArbitreController extends Controller
{
    /**
     * @Route("/arbitres",name="arbitres")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $arbitres = $em->getRepository( 'UserBundle:User' )->findArbitre();

        return $this->render( 'MainBundle:Arbitre:index.html.twig', array( 'arbitres' => $arbitres ) );
    }

    /**
     * @Route("/arbitres/ajax/becomeArbitre",name="arbitres_ajax_become_arbitre")
     */
    public function becomeArbitreAction(Request $request ) {
        if( $request->isXmlHttpRequest() ) {
            try {
                $em = $this->getDoctrine()->getManager();
                $user = $this->getUser();

                if( !in_array( 'ROLE_ARBITRE', $user->getRoles() ) ) {
                    foreach( $user->getRoles() as $k => $v )
                        $user->removeRole( $v );

                    $user->addRole( 'ROLE_ARBITRE_WAITING_APPROVAL' );
                }
                $user->setArbitreDisponibilite( $request->get( 'disponibilite' ) );

                $em->flush();

                $response = new Response( json_encode( array( 'status' => 'ok' ) ) );
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
