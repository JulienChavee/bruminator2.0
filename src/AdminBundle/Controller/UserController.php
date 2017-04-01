<?php

namespace AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class UserController extends Controller
{
    /**
     * @Route("/user", name="admin_user")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $users = $em->getRepository( 'UserBundle:User' )->findAll();

        return $this->render( 'AdminBundle:User:index.html.twig', array( 'array_users' => $users ) );
    }

    /**
     * @Route("/user/ajax/promote", name="admin_user_ajax_promote")
     */
    public function ajaxPromoteAction( Request $request ) {
        if( $request->isXmlHttpRequest() ) {
            try {
                $em = $this->getDoctrine()->getManager();
                $user = $em->getRepository( 'UserBundle:User' )->findOneBy( array( 'id' => $request->get( 'id' ) ) );

                if( $user ){
                    foreach( $user->getRoles() as $k => $v )
                        $user->removeRole( $v );

                    switch( $request->get( 'role' ) ) {
                        case 'organisateur':
                            $user->addRole( 'ROLE_ADMIN' );
                            break;
                        case 'arbitre':
                            $user->addRole( 'ROLE_ARBITRE' );
                            break;
                        case 'user':
                            $user->addRole( 'ROLE_USER' );
                            break;
                    }

                    $em->flush();

                    $response = new Response( json_encode( array( 'status' => 'ok', 'return' => $this->render( 'AdminBundle:User:userRow.html.twig', array( 'user' => $user ) )->getContent() ) ) );
                } else
                    $response = new Response( json_encode( array( 'status' => 'ko', 'message' => 'L\'utilisateur n\'existe pas', 'debug' => 'L\'utilisateur n\'existe pas' ) ) );
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
