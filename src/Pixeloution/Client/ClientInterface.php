<?php namespace Pixeloution\Client;


interface ClientInterface
{
   public function get( $uri );
   public function setUserAgent( $uastring );
}