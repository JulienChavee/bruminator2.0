<?php

namespace MainBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class DefaultController extends Controller
{
    /**
     * @Route("/")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $news = $em->getRepository( 'MainBundle:News' )->findBy( array(), array( 'date' => 'DESC' ) );

        return $this->render('MainBundle:Default:index.html.twig', array( 'news' => $news ) );
    }
}
