<?php 

    namespace Pixeloution\Random;

    include('../vendor/autoload.php');

    use \Guzzle\Http\ClientInterface;
    use \Guzzle\Http\Client;
    use \InvalidArgumentException;
    use \Pixeloution\Random\RemoteProceedureCall;


    class QuotaExceededException extends \Exception {}
    class ConnectivityException extends \Exception {}

class Randomizer2 
{
    /**
     * bitwise options for random string creation
     */
    const DIGITS     = 1;
    const UPPERCASE  = 2;
    const LOWERCASE  = 4;
    const UNIQUE     = 8;
    const ALL_STRING = 15;

    /**
     * define minimum and maximum string lengths
     */
    const MIN_STRING = 1;
    const MAX_STRING = 20;

    /**
     * random.org provided API key for access
     * @var string
     */
    protected $apiKey;

    /**
     * milliseconds of wait recommended between requests. this value
     * is set after each request to random.org
     * @var [type]
     */
    protected $suggestedDelay;

    /**
     * the unixtime of the last request
     */
    protected $lastRequestTime;

    /**
     * base URL for service. all requests must be made via POST
     * @var string
     */
    protected $service = 'https://api.random.org/json-rpc/1/invoke';

    public function __construct($apiKey, ClientInterface $client = null)
    {
        $this->apiKey = $apiKey;
        $this->client = $client ?: new \Guzzle\Http\Client();

        $this->client->setUserAgent('PixeloutionRandomLib/v2API');
        $this->client->setBaseUrl($this->service);
    }
    
    /**
     * getter/setter for apiKey - used to access random.org services
     * 
     * @param string $key
     * 
     * @return mixed
     * returns string if invoked without an argument, otherwise 
     * returns null
     */
    public function apiKey($key = null)
    {
        if(!$key)
            return $this->apiKey;

        $this->apiKey = $key;
    }

    public function sequence($min, $max)
    {
        $params = array(
                'replacement' => false
            ,   'base'        => 10
            ,   'apiKey'      => $this->apiKey
            ,   'min'         => $min
            ,   'max'         => $max
            ,   'n'           => $max - $min + 1
        );

        return $this->sendRequest('generateIntegers', $params);
    }

    public function integers($min, $max, $quantity)
    {

    }
    
    public function integer($min, $max)
    {

    }


    protected function sendRequest($method, $params)
    {
        $rpcRequest = new RemoteProceedureCall($method, $params);

        $request = $this->client->post(
                null
            ,   array('content-type' => 'application/json')
            ,   $rpcRequest->__toString()
        );

       
        $response = $request->send();

        return $response->getBody(true);
    }
}









