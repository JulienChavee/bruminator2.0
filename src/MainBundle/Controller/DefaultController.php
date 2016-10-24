<?php

namespace MainBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $news = $em->getRepository( 'MainBundle:News' )->findBy( array(), array( 'date' => 'DESC' ) );

        $teams = $em->getRepository( 'TeamBundle:Team' )->findBy( array(), array( 'inscriptionDate' => 'DESC' ));

        $matchs = $em->getRepository( 'MatchBundle:Matchs' )->findBy( array(), array( 'date' => 'DESC' ), 5 );

        return $this->render('MainBundle:Default:index.html.twig', array( 'news' => $news, 'teams' => $teams, 'matchs' => $matchs ) );
    }
}
