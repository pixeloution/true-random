<?php

    use \Pixeloution\Random\Randomizer;

class TrueRandomNetworkTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->generator = new Randomizer(
                'test@example.com'        
        );
    }

    /**
     * generate a single integer
     * 
     * @test
     */
    public function generateInteger()
    {

        $response = $this->generator->integer(35,36);

        $this->assertTrue(is_numeric($response));
        $this->assertGreaterThan(34, $response);
        $this->assertLessThan(37, $response);
    }
    
    /**
     * generate a sequence of integers allowing repeats
     * 
     * @test
     */
    public function generateIntegers()
    {
        $response = $this->generator->integers(35,36,10);

        $this->assertCount(10, $response);
        $this->assertTrue( 2 == count(array_unique($response)));    
    }

    /**
     * should create a sequence of numbers
     * 
     * @test
     */
    public function generateSequence()
    {
        $response = $this->generator->sequence(5,10);
        sort($response);

        $this->assertEquals(array(5,6,7,8,9,10), $response);
    }

    /**
     * @test
     */
    public function generateStringDigitsUppercase()
    {
        $response = $this->generator->strings(10, 1, Randomizer::DIGITS | Randomizer::UPPERCASE);
        echo "\nTesting response string {$response[0]}\n";
        $this->assertRegExp('/[\dA-Z]+/', $response[0]);
    }

    /**
     * @test
     */
    public function generateStringWithNoDigits()
    {
        $response = $this->generator->strings(20, 1, Randomizer::ALL ^ Randomizer::DIGITS);
        echo "\nTesting response string {$response[0]}\n";
        $this->assertRegExp('/[\D]+/', $response[0]);
    }
}