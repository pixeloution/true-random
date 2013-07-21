<?php namespace Pixeloution\Random;

use \Guzzle\Http\ClientInterface;
use \InvalidArgumentException;

class QuotaExceededException extends \Exception {}
class ConnectivityException extends \Exception {}

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
    protected $service = 'https://www.random.org';
    
    /**
     * generate a list of integers, 
     * @var string/printf-template
     */
    protected $integers = '/integers/?num=%d&min=%d&max=%d&col=1&base=10&format=plain&rnd=new';
    
    /**
     * generate a sequence of integers ( no repeating numbers )
     * @var string/printf-template
     */
    protected $sequence = '/sequences/?min=%d&max=%d&col=1&format=plain&rnd=new';
    
    /**
     * generate random strings
     * @var string/printf-template
     */
    protected $strings = '/strings/?num=%d&len=%d&digits=%s&upperalpha=%s&loweralpha=%s&unique=%s&format=plain&rnd=new';
    
    /**
     * check current IP address remaining quota
     * @var string
     */
    protected $quota = '/quota/?format=plain';   

    /**
     * the user agent to set for requests. random.org recommends that your email address
     * be in the UA string
     * @var string
     */
    protected $userAgent;


    /**
     * if the program should report the quota via echo before and after
     * each request. useful for testing, should probably be off in production
     * @var boolean
     */
    protected $reportQuota = false;

    /**
     * @param string $UA
     * a user-agent string - should be the user's email address as per random.org docs
     * 
     * @param object $browser
     * a browser object adhering to ClientInterface
     */
    public function __construct($UA, ClientInterface $browser = null)
    {
        $this->browser = $browser ?: new \Guzzle\Http\Client();
        $this->browser->setUserAgent( 'RandomizerLib/' . $UA );
        $this->browser->setBaseUrl  ( $this->service );
    }

    /**
     * generate a list of integers
     * 
     * @param  integer  $min      
     * @param  integer  $max      
     * @param  integer  $quantity
     *  
     * @return array
     */
    public function integers($min, $max, $quantity = 1)
    {
        return $this->fetchData(sprintf($this->integers, $quantity, $min, $max));
    }

    /**
     * randomizes the numbers from $low to $high and returns an array in random order.
     * asking for a sequence between 1, 5 returns an array with the values 1, 2, 3, 4, 5
     * in a randomized order
     *    
     * @param  integer $low  
     * @param  integer $high 
     * 
     * @return array
     */
    public function sequence($low, $high)
    {
        if($high <= $low) 
            return null;

        return $this->fetchData( sprintf($this->sequence, $low, $high) );
    }

    /**
     * creates random strings up to 20 characters in length, with options based on
     * a bitmask for allowing digits, uppercase, lowercase, and unique/not characters
     * 
     * @param  integer $length   
     * @param  integer $quantity 
     * @param  integer $opts         see class constants
     * 
     * @return array
     */
    public function strings($length, $quantity, $opts = null)
    {
        if($length > 20 || $length < 1)
            throw new \InvalidArgumentException('value must be between 1 and 20');

        if($opts === null)
             $opts = Randomizer::ALL ^ Randomizer::UNIQUE;

        # determine valid character sets for random string generated
        $digits = ($opts & Randomizer::DIGITS)    ? 'on' : 'off';
        $upper  = ($opts & Randomizer::UPPERCASE) ? 'on' : 'off';
        $lower  = ($opts & Randomizer::LOWERCASE) ? 'on' : 'off';
        $unique = ($opts & Randomizer::UNIQUE)    ? 'on' : 'off';

        return $this->fetchData( sprintf($this->strings, $quantity, $length, $digits, $upper, $lower, $unique) );
    }

    /**
     * setting this value to true causes the object to output used bits and remaining
     * bits during each request. should be left false for production
     * 
     * @param bool $setting
     */
    public function setReportQuota($setting)
    {
        $this->reportQuota = $setting;
    }

    protected function fetchData($uri)
    {
        $start = $this->checkQuota();

        $request = $this->browser->get($uri);
        $request->getCurlOptions()->set(CURLOPT_CONNECTTIMEOUT, 30);
        
        $response = $request->send();

        if( $this->reportQuota )
        {
            $end   = $this->checkQuota();
            $spent = $start - $end;

            echo "spent: $spent\n";
            echo "remaining: $end\n";
        }

        return $this->parse($response);
    }

    protected function parse($response)
    {
        if( $response->getStatusCode() !== 200 )
            throw new ConnectivityException( 'unable to fetch data from random.org' );

        $results = $response->getBody();
        
        return explode("\n", trim($results));
    }

    protected function checkQuota()
    {
        $request  = $this->browser->get($this->quota);
        $response = $request->send();

        if($response->getStatusCode() !== 200)
            throw new ConnectivityException( 'unable to fetch data from random.org' );

        $remaining = trim( $response->getBody() );
        if($remaining < self::MINIMUM_BITS_REMAINING)
            throw new QuotaExceededException('You have exceeded your quota. Visit Random.org to learn how to buy more resources');      

        return $remaining;
    }
}









