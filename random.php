<?php
/**
 * uses random.org to fetch random integers, subject to the rules, conditions and
 * limitaions set forth by random.org
 *
 * Example: 
 * foreach( TrulyRandom::get(0,1000, 2) as $integer ) {
 *    echo $integer . "\n";
 * }
 * 
 */
class TrulyRandom
{
   protected static $url   = 'http://www.random.org/integers/';
   protected static $query = '?num=%d&min=%d&max=%d&col=1&base=10&format=plain';
   
   /**
    * Gets Random Integer From API
    * 
    * @param  int $min
    * @param  int $max
    * @param  int $quantity
    * 
    * @return array
    */
   public static function get( $min, $max, $quantity = 1 )
   {  
      #
      # dependency
      #
      if( ! function_exists('curl_version') )
         throw new Exception('PHP CURL must be enabled on server to use TrulyRandom::get');

      #
      # build HTTP request URL
      # 
      $url = self::$url . sprintf( self::$query, $quantity, $min, $max );

      #
      # get the numbers from random.org
      # 
      $raw  = self::get_data( $url );
      $ints = explode( "\n", trim( $raw ) );

      # guard against odd data being returned ...
      foreach( $ints as $int )
      {
         if( ! is_numeric($int) )
            throw new Exception('random.org returned non-numeric data');
      }

      return $ints;
   }

   /**
    * calls random.org url with query string and returns the data as presented
    * 
    * @param  string $url 
    * 
    * @throws Exception
    * if unable to get a 200 response from the random data provider
    * 
    * @return string
    */
   private static function get_data( $url )
   {
      $CH = curl_init( $url );
      
      curl_setopt( $CH, CURLOPT_RETURNTRANSFER, true );
      $data = curl_exec( $CH );

      
      #
      # make sure we actually got data
      # 
      if( curl_getinfo( $CH, CURLINFO_HTTP_CODE ) !== 200 )
         throw new Exception('Unable to get valid data from random.org');

      curl_close( $CH );
      return $data;
   }
}


