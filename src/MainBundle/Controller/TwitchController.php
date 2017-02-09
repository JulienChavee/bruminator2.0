<?php

namespace MainBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class TwitchController extends Controller
{
    /**
     * @Route("/live",name="twitch")
     */
    public function indexAction()
    {
        $fileTwitch = $this->get( 'kernel' )->getCacheDir().'/twitch.tmp';
        if( !file_exists( $fileTwitch ) )
            file_put_contents( $this->get('kernel')->getCacheDir().'/twitch.tmp', '' );

        $channel = null;

        if( time() - filemtime( $fileTwitch ) > 60 || filesize( $fileTwitch ) == 0 ) {

            $channelsApi = 'https://api.twitch.tv/kraken/streams/';
            $channelName = 'bruminator_officiel';
            $clientId = '74dease2xl8ecabhx0ocfbonno2toq';
            $ch = curl_init();

            curl_setopt_array($ch, array(
                CURLOPT_HTTPHEADER => array(
                    'Client-ID:'.$clientId
                ),
                CURLOPT_SSL_VERIFYPEER=>false,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_URL => $channelsApi.$channelName
            ));

            $channel = curl_exec($ch);
            curl_close($ch);

            file_put_contents( $fileTwitch, $channel );
        } else {
            $channel = file_get_contents( $fileTwitch );
        }

        $channel = json_decode( $channel );

        return $this->render( 'MainBundle:Twitch:index.html.twig', array( 'channel' => $channel ) );
    }
}
