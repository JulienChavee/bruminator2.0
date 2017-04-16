<?php

namespace MainBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class ClassementController extends Controller
{
    /**
     * @Route("/classement", name="classement")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $teams = $em->getRepository( 'TeamBundle:Team' )->findBy( array( 'valid' => true, 'registered' => true ) );

        $classement = $this->get( 'team.control_team')->getClassement( $teams );

        return $this->render( 'MainBundle:Classement:index.html.twig', array( 'teams' => $classement ) );
    }
}
