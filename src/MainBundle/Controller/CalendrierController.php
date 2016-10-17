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
}
