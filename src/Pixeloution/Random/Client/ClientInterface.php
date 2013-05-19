<?php namespace Pixeloution\Random\Client;


interface ClientInterface
{
   public function get( $uri );
   public function setUserAgent( $uastring );
   public function setBaseUrl( $url );
}