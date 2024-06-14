<?php

namespace Northrook\Support\String;

trait BooleanFunctions
{

    /**
     * Check if a string contains only numbers
     *
     * - Returns `false` if the string contains non-numeric characters
     * - Returns `$string` cast to int if the string contains only numbers
     *
     * @param null|string  $string
     *
     * @return int|bool
     */
    public static function isNumeric( ?string $string ) : int | bool {
        return ( preg_match( '/^\d+$/', $string ?? '' ) ) ? (int) $string : false;
    }

    public static function containsValidHTML( ?string $string, ?string $mustContain = null ) : string | bool {
        // debug( $html );
        if ( !$string || ( str_starts_with( $string, '<' ) && !str_ends_with( $string, '>' ) ) ) {
            return false;
        }

        if ( $mustContain && !str_contains( $string, $mustContain ) ) {
            return false;
        }

        preg_match_all( '#<(?!meta|img|br|hr|input\b)\b([a-z]+)(?: .*)?(?<![/| ])>#iU', $string, $result );
        $openedTags = $result[ 1 ];
        preg_match_all( '#</([a-z]+)>#iU', $string, $result );
        $closedTags = $result[ 1 ];
        $len_opened = count( $openedTags );
        if ( count( $closedTags ) === $len_opened ) {
            return $string;
        }

        return false;
    }

}