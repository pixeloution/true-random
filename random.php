<?php
/**
 * uses random.org to fetch random integers, subject to the rules, conditions and
 * limitaions set forth by random.org
 *
 * Example: 
 * later
 * 
 * 
 */
class Random
{
   protected static $url = 'http://www.random.org';
   protected static $int = '/integers/?num=%d&min=%d&max=%d&col=1&base=10&format=plain&rnd=new';
   protected static $seq = '/sequences/?min=%d&max=%d&col=1&format=plain&rnd=new';
   
   # set the UserAgent; random.org recommends your email address be in the UA string
   protected $ua  = '';
   
   /**
    * makes sure we have CURL installed
    *
    * TODO: perhaps dependency injection for code that actually gets stuff from
    * the internets
    */
   public function __construct( $opts )
   {
      #
      # dependency
      #
      if( ! function_exists('curl_version') )
         throw new Exception('PHP CURL must be enabled on server to use Random::get');      

      # set the user agent if provided
      if( $opts['ua'] )
         $this->ua = $opts['ua'];
   }

   /**
    * Gets Random Integer(s) From API
    * 
    * @param  int $min
    * @param  int $max
    * @param  int $quantity
    * 
    * @return array
    */
   public function int( $min, $max, $quantity = 1 )
   {  
      #
      # build HTTP request URL
      # 
      $url = self::$url . sprintf( self::$int, $quantity, $min, $max );

      #
      # get the numbers from random.org
      # 
      $raw  = $this->_get_data( $url );
      $ints = $this->_parse( $raw ); 

      return $ints;
   }


   public function sequence( $start, $end )
   {
      #
      # build HTTP request URL
      # 
      $url = self::$url . sprintf( self::$seq, $start, $end );

      #
      # get the numbers from random.org
      # 
      $raw  = $this->_get_data( $url );
      $ints = $this->_parse( $raw ); 

      return $ints;   
   }


   protected function _parse( $raw )
   {
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
   protected function _get_data( $url )
   {
      $CH = curl_init( $url );
      
      curl_setopt( $CH, CURLOPT_RETURNTRANSFER, true );
      # random.org recommends setting a high timeout for applications that can
      # happily do so without issues
      curl_setopt( $CH, CURLOPT_CONNECTTIMEOUT, 60   );
      # random.org requests you include your email address in the user agent
      # http://www.random.org/clients/
      curl_setopt( $CH, CURLOPT_USERAGENT, $this->ua );

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
