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
        $config = $em->getRepository( 'AdminBundle:Config' )->getAll();

        return $this->render('MainBundle:Default:index.html.twig', array( 'news' => $news, 'config' => $config ) );
    }
}
