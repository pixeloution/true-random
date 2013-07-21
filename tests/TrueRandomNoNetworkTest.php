<?php

    use \Mockery;
    use \Pixeloution\Random\Randomizer;

class TrueRandomNoNetworkTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        // builds a mock of the HTTP client for testing
        $this->client = Mockery::mock(
            '\Guzzle\Http\ClientInterface'
        );

        // these are calls the client will get in the constructor
        $this->client->shouldReceive('setUserAgent', 'setBaseUrl')->times(1)->andReturn(null);

        // sets up quota check responses
        $this->addClientResponse('/quota/?format=plain', 100000);
        
        $this->generator = new Randomizer(
                'test@example.com'
            ,   $this->client          
        );
    }

    /**
     * all methods that take a min/max should throw exceptions when the min value
     * is equal to or greater then the max value
     * 
     * @test
     */
    public function invalidArgumentsToSequence()
    {
        $exceptions = $this->sendInvalidArgsTo('sequence');

        if($exceptions !== 2)
            $this->fail('Sequence did not throw an exception for invalid min/max');
    }

    /**
     * all methods that take a min/max should throw exceptions when the min value
     * is equal to or greater then the max value
     * 
     * @test
     */
    public function invalidArgumentsToInteger()
    {
        $exceptions = $this->sendInvalidArgsTo('integer');

        if($exceptions !== 2)
            $this->fail('Integer did not throw an exception for invalid min/max');
    }

    /**
     * all methods that take a min/max should throw exceptions when the min value
     * is equal to or greater then the max value
     * 
     * @test
     */
    public function invalidArgumentsToIntegers()
    {
        $exceptions = $this->sendInvalidArgsTo('integer');

        if($exceptions !== 2)
            $this->fail('Integers did not throw an exception for invalid min/max');
    }


    /**
     * should get an exception if user tries to specify a string length not within
     * the allowed bounds
     * 
     * @test
     * @expectedException InvalidArgumentException
     */
    public function invalidArgumentsToStrings()
    {
        $this->generator->strings(Randomizer::MAX_STRING + 1);
    }

    /**
     * generate a sequence of integers allowing repeats
     * 
     * @test
     */
    public function generateIntegers()
    {
        $this->addClientResponse(
                '/integers/?num=5&min=1&max=50&col=1&base=10&format=plain&rnd=new'
            ,   "7\n22\n50\n12\n7\n"
        );

        $this->assertEquals(array(7,22,50,12,7), $this->generator->integers(1,50,5));
    }

    /**
     * generate a single integer
     * 
     * @test
     */
    public function generateInteger()
    {
        $this->addClientResponse(
                '/integers/?num=1&min=1&max=50&col=1&base=10&format=plain&rnd=new'
            ,   "15\n"            
        );

        $this->assertEquals(15, $this->generator->integer(1,50));
    }

    /**
     * should create a sequence of numbers
     * 
     * @test
     */
    public function generateSequence()
    {
        $this->addClientResponse(
                '/sequences/?min=1&max=5&col=1&format=plain&rnd=new'
            ,   "5\n2\n1\n4\n3\n"
        );

        $this->assertEquals(array(5,2,1,4,3), $this->generator->sequence(1,5));
    }


    public function tearDown()
    {
        Mockery::close();
    }


    /**
     * for the methods that take min/max arguments, makes sure an exception is 
     * thrown if they are bookends or matching
     * 
     * @param  [type] $method [description]
     * @return [type]         [description]
     */
    protected function sendInvalidArgsTo($method)
    {        
        $exceptions = 0;

        try {
            $this->generator->$method(10,10);
        } catch(InvalidArgumentException $e) {
            $exceptions++;
        }

        try {
            $this->generator->$method(10,9);
        } catch(InvalidArgumentException $e) {
            $exceptions++;
        }

        return $exceptions;
    }


    protected function addClientResponse($uri, $responseBody)
    {
        // handle quota checks
        $response = $this->defaultResponseMock($responseBody);
        $request  = $this->defaultRequestMock($response);

        $this->client->shouldReceive('get')->zeroOrMoreTimes()->with($uri)->andReturn($request);        
    }

    protected function defaultResponseMock($data)
    {
        $response = Mockery::Mock('response');
        $response->shouldReceive('getStatusCode')->zeroOrMoreTimes()->andReturn(200);
        $response->shouldReceive('getBody')->zeroOrMoreTimes()->andReturn($data);

        return $response;
    }


    protected function defaultRequestMock($response)
    {
        $request = Mockery::Mock('request');
        $request->shouldReceive('send')->zeroOrMoreTimes()->andReturn($response);
        $request->shouldReceive('getCurlOptions->set')->zeroOrMoreTimes()->andReturn(null);

        return $request;
    }



    ////// TRASH
    protected function addQuotaExpectations($client)
    {
        $quotaResponse = Mockery::Mock('response');
        $quotaResponse->shouldReceive('getStatusCode')->zeroOrMoreTimes()->andReturn(200);

        $quotaRequest = Mockery::Mock('request');
        $quotaRequest->shouldReceive('send')->zeroOrMoreTimes()->andReturn($quotaRequest);

        $client->shouldReceive('get')->zeroOrMoreTimes()->with('/quota/?format=plain')->andReturn($quotaRequest);


        /*
        $x = Mockery::mock('class_x');
        $x->shouldReceive('foo')->once()->andReturn('woot');
        $y = Mockery::mock('class_y');
        $y->shouldReceive('bar')->once()->andReturn($x);

        echo $y->bar()->foo();
        */
       
        return;
       
        $client = Mockery::mock(
            '\Guzzle\Http\ClientInterface'
        );

        $client->shouldReceive('setUserAgent')->zeroOrMoreTimes()->andReturn(null);
        $client->shouldReceive('setBaseUrl')->zeroOrMoreTimes()->andReturn(null);

        // this is not going to be pretty
        #$response = Mockery::Mock('response');
        #$response->shouldReceive('getStatusCode')->andReturn(200);
        #$response->shouldReceive('getBody')->andReturn("5\n55\n22"); 
        
        #$request = Mockery::Mock('request');
        #$request->shouldReceive('send')->andReturn($response);

        $quotaResponse = Mockery::Mock('response')->shouldReceive('getStatusCode')->twice()->andReturn(200);
        $quotaResponse->shouldReceive('getBody')->zeroOrMoreTimes()->andReturn(100000);
        $quotaRequest  = Mockery::Mock('request')->shouldReceive('send')->zeroOrMoreTimes()->andReturn($quotaResponse);

        $client->shouldReceive('get')->once()->with('/quota/?format=plain')->andReturn($quotaRequest);

        #$client->shouldReceive('get')->zeroOrMoreTimes()->with('/sequences/?min=%d&max=%d&col=1&format=plain&rnd=new')->andReturn($request);
        

        
        
        return $client;
    }

}