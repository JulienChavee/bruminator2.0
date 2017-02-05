<?php

namespace AdminBundle\Twig;

use Symfony\Bridge\Doctrine\RegistryInterface;

class SynergieExtension extends \Twig_Extension {

    protected $doctrine;

    public function __construct( RegistryInterface $doctrine )
    {
        $this->doctrine = $doctrine;
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction( 'synergie', array( $this, 'getSynergie' ) ),
        );
    }

    public function getSynergie( $class1, $class2 ) {
        try {
            $em = $this->doctrine->getManager();

            $value = $em->getRepository( 'TeamBundle:SynergieClass' )->getSynergie( $class1, $class2 );

            return $value;
        } catch( \Exception $e ) {
            return null;
        }
    }

    public function getName() {
        return 'synergie_extension';
    }
}