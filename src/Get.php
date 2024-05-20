<?php

namespace Northrook\Support;

use Exception;
use Northrook\Logger\Log;
use Northrook\Support\Config\Stopwords;

class Get extends Make
{

    /**
     * Get a boolean option from an array of options.
     *
     * ⚠️ Be careful if passing other nullable values, as they will be converted to booleans.
     *
     * - Pass an array of options, `get_defined_vars()` is recommended.
     * - All 'nullable' values will be converted to booleans.
     * - `true` options set all others to false.
     * - `false` options set all others to true.
     * - Use the `$default` parameter to set value for all if none are set.
     *
     * @param array  $options  Array of options, `get_defined_vars()` is recommended
     * @param bool   $default  Default value for all options
     *
     * @return array<string, bool>
     */
    public static function booleanOptions( array $options, bool $default = true ) : array {

        // Isolate the options
        $options = array_filter( $options, static fn ( $value ) => is_bool( $value ) || is_null( $value ) );

        // If any option is true, set all others to false
        if ( in_array( true, $options, true ) ) {
            return array_map( static fn ( $option ) => $option === true, $options );
        }

        // If any option is false, set all others to true
        if ( in_array( false, $options, true ) ) {
            return array_map( static fn ( $option ) => $option !== false, $options );
        }
 
        // If none are true or false, set all to the default
        return array_map( static fn ( $option ) => $default, $options );
    }

    public static function className( ?object $class = null ) : string {
        $class = is_object( $class ) ? $class::class : debug_backtrace()[ 1 ] [ 'class' ];
        return substr( $class, strrpos( $class, '\\' ) + 1 );
    }


    public static function stopwords( string $group = 'en' ) : array {
        return Stopwords::get( $group );
    }

    public static function randomInt( int $min = 0, int $max = PHP_INT_MAX ) : int {
        try {
            return random_int( $min, $max );
        }
        catch ( Exception $e ) {
            Log::Error( $e->getMessage() );
            $length = strlen( (string) $max );
            $count  = substr( time(), -$length, $length );
            return $count >= $min ? $count : $max;
        }

    }

}