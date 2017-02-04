<?php

namespace MainBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class MapController extends Controller
{
    /**
     * @Route("/maps", name="map_view")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $maps = $em->getRepository( 'MatchBundle:Map' )->findBy( array( 'visible' => true ) );

        return $this->render( 'MainBundle:Map:index.html.twig', array( 'maps' => $maps ) );
    }
}
