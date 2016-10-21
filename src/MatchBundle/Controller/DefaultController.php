<?php

namespace MatchBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class DefaultController extends Controller
{
    /**
     * @Route("/{id}", name="match_informations")
     */
    public function informationsAction( $id )
    {
        $em = $this->getDoctrine()->getManager();

        $match = $em->getRepository( 'MatchBundle:Matchs' )->findOneBy( array( 'id' => $id ) );

        return $this->render( 'MatchBundle:Default:index.html.twig', array( 'match' => $match ) );
    }
}
