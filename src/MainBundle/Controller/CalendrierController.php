<?php

namespace MainBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class CalendrierController extends Controller
{
    /**
     * @Route("/calendrier", name="calendrier")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $time = new \DateTime();
        $matchs = $em->getRepository( 'MatchBundle:Matchs' )->findByDate( (new \DateTime())->setTimestamp( mktime( 0, 0, 0, date( "m" ), 1, date( "Y" ) ) ), array( 'field' => 'date', 'type' => 'ASC') );
        return $this->render( 'MainBundle:Calendrier:index.html.twig', array( 'time' => $time, 'matchs' => $matchs ) );
    }

    /**
     * @Route("/arbre", name="arbre")
     */
    public function arbreAction() {
        $em = $this->getDoctrine()->getManager();

        $matchs = $em->getRepository( 'MatchBundle:Matchs' )->findMatchsPhasesFinales();

        return $this->render( 'MainBundle:Calendrier:arbre.html.twig', array( 'matchs' => $matchs ) );
    }
}
