<?php

namespace MainBundle\Twig;

class RemoveAccentExtension extends \Twig_Extension {

    public function getFilters() {
        return array(
            new \Twig_SimpleFilter( 'remove_accent', array( $this, 'removeAccent' ) ),
        );
    }

    public function removeAccent( $string ) {
        return iconv('UTF-8', 'US-ASCII//TRANSLIT', $string);
    }

    public function getName() {
        return 'remove_accent_extension';
    }
}
