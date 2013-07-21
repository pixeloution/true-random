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
     * define minimum and maximum string lengths
     */
    const MIN_STRING = 1;
    const MAX_STRING = 20;

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
    public function __construct($UA = 'none@example.com', ClientInterface $browser = null)
    {
        $this->browser = $browser ?: new \Guzzle\Http\Client();
        $this->browser->setUserAgent( 'RandomizerLib/' . $UA );
        $this->browser->setBaseUrl  ( $this->service );
    }

    /**
     * returns a single integer between min/max
     * @param  [type] $min [description]
     * @param  [type] $max [description]
     * @return [type]      [description]
     */
    public function integer($min, $max)
    {
        if($min >= $max)
            throw new InvalidArgumentException('For arguments ($min,$max) min must be less than max');

        $data = $this->integers($min, $max, 1);
        return $data[0];
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
        if($min >= $max)
            throw new InvalidArgumentException('For arguments ($min,$max) min must be less than max');

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
        if($low >= $high)
            throw new InvalidArgumentException('For arguments ($low,$high) low must be less than high');

        return $this->fetchData(sprintf($this->sequence, $low, $high));
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
    public function strings($length, $quantity = 1, $opts = null)
    {
        if($length > self::MAX_STRING || $length < self::MIN_STRING)
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
     * returns remaining bits left from random.org for free usage
     * 
     * @throws QuotaExceededException
     * @return int
     */
    public function checkQuota()
    {
        $request  = $this->browser->get($this->quota);
        $response = $request->send();

        if($response->getStatusCode() !== 200)
            throw new ConnectivityException( 'unable to fetch data from random.org' );

        $remaining = trim( $response->getBody() );

        // should this really be an exception?
        if($remaining < self::MINIMUM_BITS_REMAINING)
            throw new QuotaExceededException('You have exceeded your quota. Visit Random.org to learn more');      

        return $remaining;
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

        // timeout length recommended by random.org
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
}









