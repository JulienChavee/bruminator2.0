<?php

namespace MainBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class NewsController extends Controller
{
    /**
     * @Route("/news/{id}-{slugNews}", name="news_view")
     */
    public function indexAction( $id, $slugNews )
    {
        $em = $this->getDoctrine()->getManager();
        $news = $em->getRepository( 'MainBundle:News' )->findOneBy( array( 'id' => $id ) );

        if( $news && ( $news->getDate() <= new \DateTime( 'now' ) || $this->isGranted( 'ROLE_ADMIN' ) ) ) {
            if( $this->get( 'cocur_slugify' )->slugify( $news->getTitle() ) == $slugNews )
                return $this->render('MainBundle:News:index.html.twig', array('new' => $news));
            else
                return $this->redirectToRoute( 'news_view', array( 'id' => $news->getId(), 'slugNews' => $this->get( 'cocur_slugify' )->slugify( $news->getTitle() ) ) );
        } else {
            $this->addFlash( 'danger', 'La news n\'existe pas' );

            return $this->redirectToRoute( 'homepage' );
        }
    }
}
