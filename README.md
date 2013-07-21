TrulyRandom
===========
Composer-compatible library to interact with random.org's API in order to generate truly random 
lists of integers, sequences of integers, and random alpha-numeric strings.

Random.org does limit the amount of random numbers/strings you can generate in a day, and
this program will check your remaining quota before sending requests. If you need more than
the free allowance, there are instructions on random.org for purchasing additional.

I have nothing to do with random.org other then thinking its a cool service.

## Installation
Install via Packagist
    
    "require" : 
    { 
      "pixeloution/true-random" : "*" 
    },

in your composer.json file

## Set-Up
    use Pixeloution\Random\Randomizer;

    # takes a partial User Agent as an argument; random.org requests you use your
    # email address in case of issues
    $generator = new Randomizer( 'name@example.com' );

## Generate Lists of Integers
Returns an array of non-unique integers between min, max

    $generator->integers( $minimum_value, $maximum_value, $quantity );

## Generate A Sequence of Integers
Returns an array of a integers from $start to $end, each integer appearing once.

    $generator->sequence( $start, $end );

## Generate a list of random strings
Returns an array of strings $length characters long, made up of character types
specified via bitwise options. The default value is `ALL ^ UNIQUE` 


Options are: 
* Randomizer::DIGITS  
* Randomizer::UPPERCASE
* Randomizer::LOWERCASE
* Randomizer::UNIQUE
* Randomizer::ALL 

Some examples:

    # returns all strings containing uppercase and lowercase only
    $generator->strings( $len, $qty, Randomizer::UPPERCASE | Randomizer::LOWERCASE );

    # returns lowercase strings, no repeated letters
    $generator->strings( $len, $qty, Randomizer::LOWERCASE | Randomizer::UNIQUE );

    # returns uppercase, lowercase, numeric with non-unique charaters. this is the default
    $generator->strings( $len, $qty, Randomizer::ALL ^ Randomizer::UNIQUE );    


