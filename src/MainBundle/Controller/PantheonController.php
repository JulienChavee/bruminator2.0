<?php

namespace MainBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class PantheonController extends Controller
{
    /**
     * @Route("/pantheon", name="pantheon")
     */
    public function indexAction() {
        return $this->render( 'MainBundle:Pantheon:index.html.twig' );
    }
}
