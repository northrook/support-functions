<?php

namespace Northrook\Support\Str;

use Northrook\Support\Str;

/**
 * Splitting and manipulating substrings.
 */
trait SubstringFunctions
{


    /**
     * Returns an array of positions of all occurrences of a $needle in a $string.
     *
     * @param string        $string
     * @param string|array  $match  Mach one or more $needles in a $string
     *
     * @return array
     */
    public static function posAll( string $string, string | array $match ) : array {

        $needles  = [];
        $position = 0;

        foreach ( (array) $match as $value ) {
            while ( ( $position = strpos( $string, $value, $position ) ) !== false ) {
                $needles[] = $position;
                $position  += strlen( $match );
            }
        }

        return $needles;
    }

    /**
     * Returns a substring after the first, last, or nth occurrence of a $needle in a $string.
     *
     * - The $get parameter can be negative to get the nth occurrence from the end of the string.
     *
     * @param string        $string
     * @param string|array  $match
     * @param int           $get
     *
     * @return null|string
     */
    public static function after(
        string         $string,
        string | array $match,
        int            $get = Str::FIRST,
    ) : ?string {

        $needles = Str::posAll( $string, $match );

        if ( empty( $needles ) ) {
            return $string;
        }

        if ( $get < 0 ) {
            $get = ( count( $needles ) + $get );
        }

        return substr( $string, 0, $needles[ $get ] + strlen( $match ) );
    }

    /**
     * Returns a substring before the first, last, or nth occurrence of a $needle in a $string.
     *
     * - The $get parameter can be negative to get the nth occurrence from the end of the string.
     *
     * @param string        $string
     * @param string|array  $match
     * @param int           $get
     *
     * @return null|string
     */
    public static function before(
        string         $string,
        string | array $match,
        int            $get = Str::FIRST,
    ) : ?string {

        $needles = Str::posAll( $string, $match );

        if ( empty( $needles ) ) {
            return $string;
        }

        if ( $get < 0 ) {
            $get = ( count( $needles ) + $get );
        }

        return substr( $string, 0, $needles[ $get ] );
    }

    public static function between( string $string, string $needle, ?int $max = null, ?int $after = 0 ) : string {

        $position = 0;
        $count    = [ 0 ];

        $max ??= substr_count( $string, $needle );

        for ( $iteration = 0; $iteration < $max; $iteration++ ) {

            $position = stripos( $string, $needle, $position + 1 );

            if ( $position === false ) {
                break;
            }

            $count[] = $position;

        }

        $offset = $after ? $count[ $after ] + strlen( $needle ) : 0;

        return substr( $string, $offset, $position - $offset );
    }
}