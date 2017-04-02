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

                $previousEdition = $em->getRepository( 'MainBundle:Edition' )->findLastEdition();
                $matchsPhasesFinales = $em->getRepository( 'MatchBundle:Matchs' )->findMatchsPhasesFinales( $previousEdition );

                $winners = array();

                foreach( $matchsPhasesFinales as $k => $v ) {
                    switch( $v->getType() ) {
                        case 'Finale':
                            $winners[ 'first' ] = array(
                                'team' => array(
                                    'name' => $v->getMatchResult()->getWinner()->getName(),
                                    'players' => array()
                                )
                            );

                            foreach( $v->getMatchResult()->getWinner()->getPlayers() as $k2 => $v2 ) {
                                if( !$v2->getIsRemplacant() ) {
                                    $winners[ 'first' ][ 'team' ][ 'players' ][] = array(
                                        'id' => $v2->getId(),
                                        'pseudo' => $v2->getPseudo(),
                                        'classe' => $v2->getClass()->getName()
                                    );
                                }
                            }

                            $winners[ 'second' ] = array(
                                'team' => array(
                                    'name' => $v->getMatchResult()->getWinner() == $v->getAttack() ? $v->getDefense()->getName() : $v->getAttack()->getName(),
                                    'players' => array()
                                )
                            );

                            foreach( $v->getMatchResult()->getWinner() == $v->getAttack() ? $v->getDefense()->getPlayers() : $v->getAttack()->getPlayers() as $k2 => $v2 ) {
                                if( !$v2->getIsRemplacant() ) {
                                    $winners[ 'second' ][ 'team' ][ 'players' ][] = array(
                                        'id' => $v2->getId(),
                                        'pseudo' => $v2->getPseudo(),
                                        'classe' => $v2->getClass()->getName()
                                    );
                                }
                            }

                            break;

                        case 'Petite finale':
                            $winners[ 'third' ] = array(
                                'team' => array(
                                    'name' => $v->getMatchResult()->getWinner()->getName(),
                                    'players' => array()
                                )
                            );

                            foreach( $v->getMatchResult()->getWinner()->getPlayers() as $k2 => $v2 ) {
                                if( !$v2->getIsRemplacant() ) {
                                    $winners[ 'third' ][ 'team' ][ 'players' ][] = array(
                                        'id' => $v2->getId(),
                                        'pseudo' => $v2->getPseudo(),
                                        'classe' => $v2->getClass()->getName()
                                    );
                                }
                            }
                            break;
                    }
                }

                $edition = new Edition();
                $edition->setName( $request->get( 'name' ) );
                $edition->setData( array( 'date' => json_decode( $request->get( 'date' ) ) ) );

                $previousEdition->setData( array_merge( $previousEdition->getData(), $winners ) );

                $em->persist( $edition );
                $em->flush();
                $em->getRepository( 'TeamBundle:Team' )->resetRegistration();

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
