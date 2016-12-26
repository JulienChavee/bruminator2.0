<?php

namespace AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class ClasseController extends Controller
{
    /**
     * @Route("/classe", name="admin_classe")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $array_classes = $em->getRepository( 'TeamBundle:Classe' )->findAll();

        return $this->render( 'AdminBundle:Classe:index.html.twig', array( 'array_classes' => $array_classes ) );
    }

    /**
     * @Route("/classe/ajax/getSynergie", name="admin_classe_ajax_getsynergie")
     */
    public function ajaxGetSynergieAction( Request $request ) {
        if( $request->isXmlHttpRequest() ) {
            try {
                $em = $this->getDoctrine()->getManager();

                $synergie = $em->getRepository( 'TeamBundle:SynergieClass' )->getSynergie( $request->get( 'class1' ), $request->get( 'class2' ) );

                $response = new Response( json_encode( array( 'status' => 'ok', 'return' => $synergie ) ) );
            }
            catch( \Doctrine\ORM\NoResultException $e ) {
                $response = new Response( json_encode( array( 'status' => 'ok', 'return' => 0 ) ) );
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
     * @Route("/classe/ajax/editSynergie", name="admin_classe_ajax_editsynergie")
     */
    public function ajaxEditSynergieAction( Request $request ) {
        if( $request->isXmlHttpRequest() ) {
            try {
                $em = $this->getDoctrine()->getManager();

                $em->getRepository( 'TeamBundle:SynergieClass' )->editSynergie( $request->get( 'class1' ), $request->get( 'class2' ), $request->get( 'synergie' ) );

                $response = new Response( json_encode( array( 'status' => 'ok', 'return' => $request->get( 'synergie' ) ) ) );
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
     * @Route("/classe/ajax/getClasse", name="admin_classe_ajax_getclasse")
     */
    public function ajaxGetClasseAction( Request $request ) {
        if( $request->isXmlHttpRequest() ) {
            try {
                $em = $this->getDoctrine()->getManager();

                $classe = $em->getRepository( 'TeamBundle:Classe' )->findOneBy( array( 'id' => $request->get( 'id' ) ) );
                $normalizer  = new ObjectNormalizer();;
                $normalizer->setCircularReferenceHandler(function ($object) {
                    return $object->getId();
                });
                $serializer = new Serializer( array( $normalizer ) );
                $classe = $serializer->normalize( $classe );

                $response = new Response( json_encode( array( 'status' => 'ok', 'classe' => $classe ) ) );
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
     * @Route("/classe/ajax/editClasse", name="admin_classe_ajax_editclasse")
     */
    public function ajaxEditClasseeAction( Request $request ) {
        if( $request->isXmlHttpRequest() ) {
            try {
                $em = $this->getDoctrine()->getManager();

                $classe = $em->getRepository( 'TeamBundle:Classe' )->findOneBy( array( 'id' => $request->get( 'id' ) ) );

                $classe->setName( $request->get( 'name' ) );
                $classe->setShortName( $request->get( 'shortname' ) );
                $classe->setPoints( $request->get( 'points' ) );
                // TODO : Ajouter la possibilité de bannir des classes et de changer l'état de pillier

                $em->flush();
                $response = new Response( json_encode( array( 'status' => 'ok', 'return' => $this->render( 'AdminBundle:Classe:classRow.html.twig', array( 'class' => $classe ) )->getContent() ) ) );
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
