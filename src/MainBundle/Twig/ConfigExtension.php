<?php

namespace MainBundle\Twig;

use Symfony\Bridge\Doctrine\RegistryInterface;

class ConfigExtension extends \Twig_Extension {

    protected $doctrine;

    public function __construct( RegistryInterface $doctrine )
    {
        $this->doctrine = $doctrine;
    }

    public function getFilters() {
        return array(
            new \Twig_SimpleFilter( 'config', array( $this, 'getValue' ) ),
        );
    }

    public function getValue( $name ) {
        $em = $this->doctrine->getManager();

        $value = $em->getRepository( 'AdminBundle:Config' )->getOneBy( array( 'name' => $name ) );

        if( json_decode( $value ) )
            return json_decode( $value, true );
        else
            return $value;
    }

    public function getName() {
        return 'config_extension';
    }
}
