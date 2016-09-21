<?php

namespace AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use MainBundle\Entity\News;

class NewsController extends Controller
{
    /**
     * @Route("/news", name="admin_news")
     */
    public function indexAction() {
        $em = $this->getDoctrine()->getManager();
        $array_news = $em->getRepository( 'MainBundle:News' )->findAll();

        return $this->render( 'AdminBundle:News:index.html.twig', array( 'array_news' => $array_news ) );
    }

    /**
     * @Route("/news/ajax/add", name="admin_news_ajax_add")
     */
    public function ajaxAddAction( Request $request ) {
        if( $request->isXmlHttpRequest() ) {
            try {
                $em = $this->getDoctrine()->getManager();
                $user = $this->getUser();
                $news = new News();
                $news->setAuthor( $user );
                $news->setTitle( $request->get( 'title' ) );
                $news->setMessage( $request->get( 'message' ) );
                $news->setDate( empty( $request->get( 'date' ) ) ? new \DateTime() : \DateTime::createFromFormat( 'd/m/Y H:i', $request->get( 'date' ) ) );
                $errors = $this->get( 'validator' )->validate( $news );
                if( count( $errors ) == 0 ) {
                    $em->persist( $news );
                    $em->flush();
                    $response = new Response( json_encode( array( 'status' => 'ok', 'return' => $this->render( 'AdminBundle:News:newsRow.html.twig', array( 'news' => $news, 'loop' => array( 'index' => '-' ) ) )->getContent() ) ) );
                } else
                    $response = new Response( json_encode( array( 'status' => 'ko', 'message' => 'Impossible d\'ajouter la news', 'errors' => $this->render( 'AdminBundle:validation.html.twig', array( 'errors' => $errors ) )->getContent(), 'debug' => $errors ) ) );
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
     * @Route("/news/ajax/delete", name="admin_news_ajax_delete")
     */
    public function ajaxDeleteAction( Request $request ) {
        if( $request->isXmlHttpRequest() ) {
            try {
                $em = $this->getDoctrine()->getManager();
                $news = $em->getRepository( 'MainBundle:News' )->findOneBy( array( 'id' => $request->get( 'id' ) ) );

                if( $news ) {
                    $em->remove( $news );
                    $em->flush();
                    $response = new Response( json_encode( array( 'status' => 'ok' ) ) );
                } else
                    $response = new Response( json_encode( array( 'status' => 'ko', 'debug' => 'La news n\'existe pas' ) ) );
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
     * @Route("/news/ajax/get", name="admin_news_ajax_get")
     */
    public function ajaxGetAction( Request $request ) {
        if( $request->isXmlHttpRequest() ) {
            try {
                $em = $this->getDoctrine()->getManager();
                $news = $em->getRepository( 'MainBundle:News' )->findOneBy( array( 'id' => $request->get( 'id' ) ) );
                $normalizer  = new ObjectNormalizer();;
                $normalizer->setCircularReferenceHandler(function ($object) {
                    return $object->getId();
                });
                $serializer = new Serializer( array( $normalizer ) );
                $news = $serializer->normalize( $news );
                $response = new Response( json_encode( array( 'status' => 'ok', 'news' => $news ) ) );
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
     * @Route("/news/ajax/edit", name="admin_news_ajax_edit")
     */
    public function ajaxEditAction( Request $request ) {
        if( $request->isXmlHttpRequest() ) {
            try {
                $em = $this->getDoctrine()->getManager();

                $news = $em->getRepository( 'MainBundle:News' )->findOneBy( array( 'id' => $request->get( 'id' ) ) );
                $news->setTitle( $request->get( 'title' ) );
                $news->setMessage( $request->get( 'message' ) );
                $news->setDate( empty( $request->get( 'date' ) ) ? new \DateTime() : \DateTime::createFromFormat( 'd/m/Y H:i', $request->get( 'date' ) ) );
                $errors = $this->get( 'validator' )->validate( $news );
                if( count( $errors ) == 0 ) {
                    $em->flush();
                    $response = new Response(json_encode(array('status' => 'ok', 'return' => $this->render('AdminBundle:News:newsRow.html.twig', array('news' => $news))->getContent())));
                } else
                    $response = new Response( json_encode( array( 'status' => 'ko', 'message' => 'Impossible de modifier la news', 'errors' => $this->render( 'AdminBundle:News:validation.html.twig', array( 'errors' => $errors ) )->getContent(), 'debug' => $errors ) ) );
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
