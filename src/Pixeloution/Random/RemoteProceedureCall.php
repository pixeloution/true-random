<?php

   namespace \Pixeloution\Random;

class RemoteProceedureCall
{
   public function __construct( $method, $params )
   {
      $this->validateMethod($method);
      $this->validateParams($params);   
   }
   // should these be self-validating objects?!
   protected function rpcRequest($method, $params, $id = null)
   {
        if(!$id)
            $id = uniqid();

        return json_encode(array(
                'jsonrpc' => '2.0'
            ,   'method'  => $method
            ,   'params'  => $params
            ,   'id'      => $id
        ));
    }   
}