<?php

namespace Northrook\src;

class Is
{

    public static function number( mixed $value ) : bool {
        return is_int( $value ) || is_float( $value );
    }

    public static function email( mixed $value ) : string | bool {
        if ( !filter_var( $value, FILTER_VALIDATE_EMAIL ) ) {
            return false;
        }
        return trim( $value );
    }
}