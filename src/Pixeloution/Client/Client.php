<?php namespace Pixeloution\Client;

/**
 * placeholder implementation to force Guzzle to use ClientInterface; allows
 * for easily swapping out client libraries as long as they contain the methods
 * that Randomizer uses
 */
class Client extends Guzzle\Http\Client implements ClientInterface
{

   
}