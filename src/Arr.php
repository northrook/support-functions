<?php

namespace Northrook\Support;

use JsonException;
use Northrook\Support\Functions\ArrayFunctions;

/**
 * TODO [low] Integrate features from https://github.com/adbario/php-dot-notation/blob/3.x/src/Dot.php
 *   ✅ Dot notation
 */
final class Arr
{

    use ArrayFunctions;

    private mixed $array = [];

    /**
     * Create a new Dot instance
     *
     *
     * @param mixed             $items
     * @param non-empty-string  $delimiter
     *
     * @return void
     */
    private function __construct( mixed $items, readonly private string $delimiter = '.' ) {
        $this->set( $this->getArrayItems( $items ) );
    }

    public static function dot( mixed $items, bool $getObject = false, string $delimiter = '.' ) : array | Arr {
        return new Arr( $items, $delimiter );
    }

    /**
     * Return the given items as an array
     *
     *
     * @param mixed  $items
     *
     * @return array
     */
    protected function getArrayItems( mixed $items ) : array {
        if ( is_array( $items ) ) {
            return $items;
        }

        if ( $items instanceof Arr ) {
            return $items->array;
        }

        return (array) $items;
    }

    public function all( bool $filter = false ) : array {
        return $filter ? array_filter( $this->array ) : $this->array;
    }

    /**
     * Set a given key / value pair or pairs
     *
     *
     * @param int|array|string  $keys
     * @param mixed|null        $value
     *
     * @return $this
     */
    public function set( int | array | string $keys, mixed $value = null ) : Arr {
        if ( is_array( $keys ) ) {
            foreach ( $keys as $key => $array ) {
                $this->set( $key, $array );
            }

            return $this;
        }

        $items = &$this->array;

        if ( is_string( $keys ) ) {
            foreach ( explode( $this->delimiter, $keys ) as $key ) {
                if (
                    !isset( $items[ $key ] )
                    || !is_array( $items[ $key ] )
                ) {
                    $items[ $key ] = [];
                }

                $items = &$items[ $key ];
            }
        }

        $items = $value;

        return $this;
    }

    /**
     * Return the value of a given key
     *
     *
     * @param null|int|string  $key
     * @param mixed            $default
     *
     * @return mixed
     */
    public function get( null | int | string $key = null, mixed $default = null ) : mixed {
        if ( $key === null ) {
            return $this->array;
        }

        if ( array_key_exists( $key, $this->array ) ) {
            return $this->array[ $key ];
        }

        if ( !is_string( $key ) || !str_contains( $key, $this->delimiter ) ) {
            return $default;
        }

        $items = $this->array;

        foreach ( explode( $this->delimiter, $key ) as $segment ) {
            if ( !is_array( $items ) || !array_key_exists( $segment, $items ) ) {
                return $default;
            }

            $items = &$items[ $segment ];
        }

        return $items;
    }

    /**
     * Set a given key / value pair or pairs
     * if the key doesn't exist already
     *
     *
     * @param array|int|string  $keys
     * @param mixed             $value
     *
     * @return $this
     */
    public function add( array | int | string $keys, mixed $value = null ) : Arr {
        if ( is_array( $keys ) ) {
            foreach ( $keys as $key => $array ) {
                $this->add( $key, $array );
            }

        }
        elseif ( $this->get( $keys ) === null ) {
            $this->set( $keys, $value );
        }

        return $this;
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

    public static function unique( array $array ) : array {
        return array_flip( array_flip( $array ) );
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