<?php
/**
 * uses random.org to fetch random integers, subject to the rules, conditions and
 * limitaions set forth by random.org
 *
 * Example: 
 * $random  = new Random();
 *
 * 
 * #### ALL USES BELOW return an array of results ####
 *
 * 
 * # generate strings 3 characters long, only from lower case chars ( 10 strings )
 * $strings = $random->string( 3, 10, Random::LOWERCASE );
 *
 * # generate strings only from lowercase, and do not reuse chars within a string
 * $strings = $random->string( 3, 10, Random::UNIQUE );
 *
 * # generate unique strings, with upper and lower, but no digits
 * $strings = $random->string( 3, 10, Random::ALL ^ Random::DIGITS )
 *
 * # generate unique, upper, lower, numeric strings
 * $strings = $random->string( 3, 10, Random::ALL );
 * 
 */
class Random
{
   # used in string creation to specify what type of strings are wanted
   const DIGITS    = 1;
   const UPPERCASE = 2;
   const LOWERCASE = 4;
   const UNIQUE    = 8;
   const ALL       = 15;

   # if below this amount remains, calls to random.org will fail. all IP addresses
   # start with 1,000,000 bits and regenerate at the rate of 200,000 per day
   const MINIMUM_BITS_REMAINING = 25000;

   protected static $url = 'http://www.random.org';
   protected static $int = '/integers/?num=%d&min=%d&max=%d&col=1&base=10&format=plain&rnd=new';
   protected static $seq = '/sequences/?min=%d&max=%d&col=1&format=plain&rnd=new';
   protected static $str = '/strings/?num=%d&len=%d&digits=%s&upperalpha=%s&loweralpha=%s&unique=%s&format=plain&rnd=new';
   protected static $qta = '/quota/?format=plain';
   
   # set the UserAgent; random.org recommends your email address be in the UA string
   protected $ua  = '';
   
   /**
    * makes sure we have CURL installed
    *
    * TODO: perhaps dependency injection for code that actually gets stuff from
    * the internets
    */
   public function __construct( $opts = [] )
   {
      #
      # dependency
      #
      if( ! function_exists('curl_version') )
         throw new Exception('PHP CURL must be enabled on server to use Random::get');      

      # set the user agent if provided
      if( isset($opts['ua']) )
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
      $raw  = $this->_fetch( $url );
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
      $raw  = $this->_fetch( $url );
      $ints = $this->_parse( $raw ); 

      return $ints;   
   }


   public function string( $length, $quantity, $opts = Random::ALL )
   {
      # determine valid character sets for random string generated
      $digits = ( $opts & Random::DIGITS )    ? 'on' : 'off';
      $upper  = ( $opts & Random::UPPERCASE ) ? 'on' : 'off';
      $lower  = ( $opts & Random::LOWERCASE ) ? 'on' : 'off';
      $unique = ( $opts & Random::UNIQUE )    ? 'on' : 'off';

      # 
      # build HTTP request URL
      # 
      $url = self::$url . sprintf( self::$str, $quantity, $length, $digits, $upper, $lower, $unique );

      # 
      # grab the strings from random.org
      # 
      $raw = $this->_fetch( $url );

      return explode( "\n", trim( $raw ) );
   }


   protected function _fetch( $url ) 
   {
      # there are quote limits on a per IP address basis for random.org usage - this
      # checks that we still have quota left before making a call
      $remaining = trim( $this->_get_data(self::$url . self::$qta) );
      
      if( $remaining < self::MINIMUM_BITS_REMAINING )
         throw new QuotaExceededException('You have exceeded your quota. Visit Random.org to learn how to buy more resources');

      return $this->_get_data( $url );
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

class QuotaExceededException extends Exception {}