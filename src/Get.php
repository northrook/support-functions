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
     * - Pass an array of options, where each boolean is null by default.
     * - `true` options set all others to false.
     * - `false` options set all others to true.
     * - Use the $default parameter to set value for all if none are set.
     *
     * @param array  $options
     * @param bool   $default
     *
     * @return array
     */
    public static function booleanOptions( array $options, bool $default = true ) : array {

        // Isolate the options
        $options = array_filter( $options, static fn ( $value ) => ( is_bool( $value ) || is_null( $value ) ) );

        // Check if any option is true
        if ( in_array( $default, $options, true ) ) {
            $options = array_map( static fn ( $param ) => $param === $default, $options );
        }
        else {
            $options = array_map( static fn ( $param ) => $param !== $default, $options );
        }

        return $options;
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