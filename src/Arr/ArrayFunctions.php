<?php

namespace Northrook\src\Arr;

use JsonException;
use Northrook\src\Arr;
use Northrook\src\Str;

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


    public static function update( array $array, array...$arrays ) : array {
        return Arr::mergeNested( $array, ...$arrays );
    }

    public static function mergeNested( array...$arrays ) : array {
        $merged = [];
        foreach ( $arrays as $array ) {

            if ( !is_array( $array ) ) {
                continue;
            }

            foreach ( $array as $index => $value ) {
                if ( isset( $merged[ $index ] ) && is_array( $value ) && is_array( $merged[ $index ] ) ) {
                    $merged[ $index ] = Arr::mergeNested( $merged[ $index ], $value );
                }
                else {
                    $merged[ $index ] = $value;
                }

            }
        }

        return $merged;
    }

    public static function replaceKey( array $array, string $target, string $replacement ) : array {
        $keys  = array_keys( $array );
        $index = array_search( $target, $keys, true );

        if ( $index !== false ) {
            $keys[ $index ] = $replacement;
            $array          = array_combine( $keys, $array );
        }

        return $array;
    }

    public static function searchKeys( array $array, string | array $key ) : array {

        $key = (array) $key;
        $get = [];


        foreach ( $key as $match ) {

            if ( isset( $array[ $match ] ) ) {
                $get[ $match ] = $array[ $match ];
            }

        }

        return $get;


    }

    /** Implode array to string, omitting empty values
     *
     * @param array              $array
     * @param string             $separator
     * @param bool               $withKeys
     * @param null|string|array  $wrap
     *
     * @return string
     */
    public static function implode(
        array                 $array,
        string                $separator = '',
        bool                  $withKeys = false,
        string | array | null $wrap = null,
    ) : string {

        $array = array_filter( $array );

        foreach ( $array as $key => $value ) {
            if ( is_array( $value ) ) {
                $value = Arr::implode( $value, $separator, $withKeys, $wrap );
            }
            if ( $wrap !== null ) {

                if ( is_array( $wrap ) ) {
                    $value = $wrap[ 0 ] . $key . $wrap[ 1 ];
                }
                elseif ( is_string( $wrap ) && !Str::contains( $wrap, [ ' ', '-', '_', '/', '\\', ':', ';' ] ) ) {
                    $value = "<$wrap>" . $value . "</$wrap>";
                }
            }

            if ( $withKeys ) {
                $array[ $key ] = "$key$value";
            }
            else {
                $array[ $key ] = $value;
            }
        }

        $string = implode( $separator, $array );

        return trim( $string );
    }

    public static function explode( string $separator, string $string, bool $unique = false ) : array {
        $array = explode( $separator, $string ) ?? [];
        foreach ( $array as $key => $value ) {
            $value = trim( $value );
            if ( $value ) {
                $array[ $key ] = $value;
            }
            else {
                unset( $array[ $key ] );
            }
        }

        if ( $unique ) {
            $array = Arr::unique( $array );
        }

        return $array;
    }

    public static function flatten( array $array, bool $filter = false, bool $unique = false ) : array {
        $result = [];

        if ( $filter ) {
            array_walk_recursive(
                $array,
                static function ( $item ) use ( &$result ) {
                    if ( !empty( $item ) ) {
                        $result[] = $item;
                    }

                },
            );
        }
        else {
            array_walk_recursive(
                $array,
                static function ( $item ) use ( &$result ) {
                    $result[] = $item;
                },
            );
        }

        if ( $unique ) {
            $result = Arr::unique( $result );
        }

        return $result;
    }

    public static function hasKeys( array $array, array $keys ) : bool {

        foreach ( $keys as $key ) {
            if ( !array_key_exists( $key, $array ) ) {
                return false;
            }
        }

        return true;
    }

    public static function asObject( array | object $array, bool $filter = false ) : object {

        if ( $filter && is_array( $array ) ) {
            $array = array_filter( $array );
        }

        try {
            return json_decode(
                json_encode( $array, JSON_THROW_ON_ERROR | JSON_FORCE_OBJECT ), false, 512, JSON_THROW_ON_ERROR,
            );
        }
        catch ( JsonException ) {
            return (object) $array;
        }
    }
}