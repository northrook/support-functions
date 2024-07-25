<?php

namespace Northrook\Support\Arr;

use Northrook\Support\Arr;
use Northrook\Support\Str;

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
}