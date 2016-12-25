<?php

namespace AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class ClasseController extends Controller
{
    /**
     * @Route("/classe", name="admin_classe")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $array_classes = $em->getRepository( 'TeamBundle:Classe' )->findAll();

        return $this->render( 'AdminBundle:Classe:index.html.twig', array( 'array_classes' => $array_classes ) );
    }
}
