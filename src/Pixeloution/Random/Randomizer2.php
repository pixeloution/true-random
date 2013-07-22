<?php 

    namespace Pixeloution\Random;

    use \Guzzle\Http\ClientInterface;
    use \InvalidArgumentException;

    class QuotaExceededException extends \Exception {}
    class ConnectivityException extends \Exception {}

class Randomizer2 
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

    
}









