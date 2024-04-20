<?php

namespace Northrook\Support;

use Exception;
use Northrook\Logger\Log;
use Northrook\Support\Config\Stopwords;

class Get extends Make
{

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