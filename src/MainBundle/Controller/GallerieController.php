<?php

namespace MainBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class GallerieController extends Controller
{
    /**
     * @Route("/musee", name="gallerie")
     */
    public function indexAction()
    {
        $res = json_decode( file_get_contents( 'https://concours.bruminator.eu/results.php' ), true );

        return $this->render( 'MainBundle:Gallerie:index.html.twig', array( 'res' => $res ) );
    }
}
