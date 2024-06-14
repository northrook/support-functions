<?php

namespace Northrook\Support;

final class Func
{

    /**
     * @param iterable     $iterable
     * @param callable     $callback
     * @param string|bool  $implode
     *
     * @return null|string|array
     */
    public static function forEach( iterable $iterable, callable $callback, string | bool $implode = false,
    ) : string | array | null {

        $return = [];

        foreach ( $iterable as $key => $value ) {
            $return[] = $callback( $value, $key );
        }

        if ( $implode ) {
            return implode( $implode === true ? '' : $implode, $return );
        }

        return $return;
    }

    /**
     * Parse a Class[@]method style callback into class and method.
     *
     *
     * @param string       $callback
     * @param string|null  $default
     *
     * @return string[]
     */
    public static function parseCallback( string $callback, ?string $default = null ) : array {
        return Str::contains( $callback, '@' ) ? explode( '@', $callback, 2 ) : [ $callback, $default ];
    }

}