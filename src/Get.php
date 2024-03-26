<?php

namespace Northrook\Support;

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

}