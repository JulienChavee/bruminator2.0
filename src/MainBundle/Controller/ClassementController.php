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

        $teams = $em->getRepository( 'TeamBundle:Team' )->findBy( array( 'valid' => true ) );

        $classement = array();

        foreach( $teams as $k => $v ) {
            $res = $this->get( 'team.control_team' )->getPoints( $v, false );

            $classement[ 'team' ][ $k ] = $v;
            $classement[ 'nb_match' ][ $k ] = $res[ 'nb_match' ];
            $classement[ 'pointsSuisse' ][ $k ] = $res[ 'pointsSuisse' ];
            $classement[ 'pointsGoulta' ][ $k ] = $res[ 'pointsGoulta' ];
            $classement[ 'pointsSuisseAdverse' ][ $k ] = $res[ 'pointsSuisseAdverse' ];
            $classement[ 'pointsGoultaAdverse' ][ $k ] = $res[ 'pointsGoultaAdverse' ];
        }
        array_multisort( $classement[ 'pointsSuisse' ], SORT_DESC, $classement[ 'pointsGoulta' ], SORT_DESC, $classement[ 'pointsSuisseAdverse' ], SORT_DESC, $classement[ 'pointsGoultaAdverse' ], SORT_DESC, $classement[ 'nb_match' ], SORT_DESC, $classement[ 'team' ], SORT_DESC);

        return $this->render( 'MainBundle:Classement:index.html.twig', array( 'teams' => $classement ) );
    }
}
