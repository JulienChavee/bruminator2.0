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

        $nbTeam = count( $em->getRepository( 'TeamBundle:Team' )->findAll() );
        $nbTeamValid = count( $em->getRepository( 'TeamBundle:Team' )->findBy( array( 'valid' => 1 ) ) );

        return $this->render( 'AdminBundle:Match:index.html.twig', array( 'matchCreated' => $this->get( 'match.control_match' )->matchCreated(), 'nbTeam' => $nbTeam, 'nbTeamValid' => $nbTeamValid ) );
    }

    /**
     * @Route("/match/ajax/generate", name="admin_match_ajax_generate")
     */
    public function ajaxGenerateMatchAction( Request $request ) {
        // TODO : Vérifier ajax et si la fin des inscription est < now()
        $response = new Response( $this->get( 'match.control_match' )->generateMatch() );
        $response->headers->set( 'Content-Type', 'application/json' );

        return $response;
    }
}
