<?php namespace Pixeloution\Random;

use Pixeloution\Random\Client\ClientInterface;
use Pixeloution\Random\Client\Client;

class Randomizer 
{
   /**
    * bitwise options for random string creation
    */
   const DIGITS    = 1;
   const UPPERCASE = 2;
   const LOWERCASE = 4;
   const UNIQUE    = 8;
   const ALL       = 15;

   /**
    * automatically fails calls to random.org when fewer then this number of bits
    * remaing available to the calling IP address. random.org grants users 1M bits
    * and regenerates those bits at 200K per day.
    */
   const MINIMUM_BITS_REMAINING = 25000;

   /**
    * base URL for service
    * @var string
    */
   protected static $base = 'http://www.random.org';
   
   /**
    * generate a list of integers, 
    * @var string/printf-template
    */
   protected static $integers = '/integers/?num=%d&min=%d&max=%d&col=1&base=10&format=plain&rnd=new';
   
   /**
    * generate a sequence of integers ( no repeating numbers )
    * @var string/printf-template
    */
   protected static $sequence = '/sequences/?min=%d&max=%d&col=1&format=plain&rnd=new';
   
   /**
    * generate random strings
    * @var string/printf-template
    */
   protected static $strings = '/strings/?num=%d&len=%d&digits=%s&upperalpha=%s&loweralpha=%s&unique=%s&format=plain&rnd=new';
   
   /**
    * check current IP address remaining quota
    * @var string
    */
   protected static $quota = '/quota/?format=plain';   

   /**
    * the user agent to set for requests. random.org recommends that your email address
    * be in the UA string
    * @var string
    */
   protected $userAgent;

   /**
    * disable/enable quota checks before requests
    * @var [type]
    */
   protected $checkQuota = true;


   /**
    * @param object $browser
    */
   public function __construct( $userAgent, ClientInterface $browser = null )
   {
      $this->browser = $browser ?: new Client();
   }
}









