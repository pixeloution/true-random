TrulyRandom
===========
Composer-based library to interact with random.org's API in order to generate truly random 
lists of integers, sequences of integers, and random alpha-numeric strings.

Random.org does limit the amount of random numbers/strings you can generate in a day, and
this program will check your remaining quota before sending requests. If you need more than
the free allowance, there are instructions on random.org for purchasing additional.

## Installation
[ composer instructions ]

## Set-Up
    use Pixeloution\Random\Randomizer;

    # takes a partial User Agent as an argument; random.org requests you use your
    # email address in case of issues
    $generator = new Randomizer( 'name@example.com' );

## Generate Lists of Integers
Returns an array of non-unique integers between min, max

    $array = $generator->integers( $minimum_value, $maximum_value, $quantity );

## Generate A Sequence of Integers
Returns an array of a integers from $start to $end, each integer appearing once.

   $array = $generator->sequence( $start, $end );

## Generate a list of random strings
Returns an array of strings $length characters long, made up of the characters specified
in the options with bitwise operators. Default is ALL ^ UNIQUE

Options are: 
* Randomizer::DIGITS  
* Randomizer::UPPERCASE
* Randomizer::LOWERCASE
* Randomizer::UNIQUE 
* Randomizer::ALL 

    # returns all strings containing uppercase and lowercase only
    $array = $generator->strings( $length, $quantity, Randomizer::UPPERCASE | Randomizer::LOWERCASE );

    # returns lowercase strings, no repeated letters
    $array = $generator->strings( $length, $quantity, Randomizer::LOWERCASE | Randomizer::UNIQUE );

    # returns uppercase, lowercase, numeric with non-unique charaters. this is the default
    $array = $generator->strings( $length, $quantity, Randomizer::ALL ^ Randomizer::UNIQUE );    


