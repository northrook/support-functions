<?php

namespace Northrook\Support\Functions;

trait ArrayFunctions
{

    /**
     * @param array              $list
     * @param array|string|null  $assign
     * @param string             $separator
     * @param bool               $filter
     *
     * @return array
     */
    public static function assignVariables(
        array                 $list,
        array | string | null $assign,
        string                $separator = ':',
        bool                  $filter = true,
    ) : array {

        if ( is_null( $assign ) ) {
            return $list;
        }

        if ( is_string( $assign ) ) {
            $assign = explode( $separator, $assign, count( $list ) );
        }

        if ( $filter ) {
            $assign = array_filter( $assign );
        }

        return array_replace( $list, $assign );
    }

    public static function autoSpread( array $array ) : array {
        if ( count( $array ) === 1 && is_array( $array[ 0 ] ) ) {
            return $array[ 0 ];
        }
        return $array;
    }

    // TODO [low] Add option for match any, match all, and match none.
    public static function keyExists( mixed $array, array $keys ) : bool {

        if ( false === is_array( $array ) ) {
            return false;
        }

        foreach ( $keys as $key ) {
            if ( !array_key_exists( $key, $array ) ) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array        $array
     * @param mixed        $value
     * @param null|string  $condition  = 'contains' | 'startsWith' | 'endsWith'
     *
     * @return bool|string
     */
    public static function has( array $array, mixed $value, ?string $condition = 'contains' ) : bool | string {

        if ( !$array ) {
            return false;
        }

        foreach ( $array as $item ) {
            if ( $condition === 'contains' && str_contains( $item, $value ) ) {
                return $item;
            }

            if ( $condition === 'startsWith' && str_starts_with( $item, $value ) ) {
                return $item;
            }

            if ( $condition === 'endsWith' && str_ends_with( $item, $value ) ) {
                return $item;
            }

            if ( $item === $value ) {
                return $item;
            }
        }

        return false;

    }
}