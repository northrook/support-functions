<?php

namespace Northrook\src\Arr;

/**
 * Dot
 *
 * This class provides a dot notation access and helper functions for
 * working with arrays of data. Inspired by Laravel Collection.
 *
 * @template Key of array-key
 * @template Value mixed
 *
 * @implements \ArrayAccess<Key, Value>
 * @implements \IteratorAggregate<Key, Value>
 */
final class DotArray implements \ArrayAccess, \Countable, \IteratorAggregate, \JsonSerializable
{
    /**
     * The stored items
     *
     * @var array<Key, Value>
     */
    protected array $items = [];


    /**
     * Create a new Dot instance
     *
     * @param mixed             $items
     * @param bool              $parse
     * @param non-empty-string  $delimiter  [.] The character to use as a delimiter
     *
     * @return void
     */
    public function __construct(
        array                     $items = [],
        bool                      $parse = false,
        protected readonly string $delimiter = ".",
    ) {
        $items = $this->getArrayItems( $items );

        if ( $parse ) {
            $this->set( $items );
        }
        else {
            $this->items = $items;
        }
    }

    /**
     * Set a given key / value pair or pairs
     * if the key doesn't exist already
     *
     * @param array<Key, Value>|string<Key  $key
     * @param mixed<Value>                  $value
     *
     * @return $this
     */
    public function add( array | string $key, mixed $value = null ) : DotArray {

        if ( is_array( $key ) ) {
            foreach ( $key as $k => $v ) {
                $this->add( $k, $v );
            }
        }
        elseif ( $this->get( $key ) === null ) {
            $this->set( $key, $value );
        }

        return $this;
    }

    /**
     * Return all the stored items
     *
     * @return array<Key, Value>
     */
    public function all() : array {
        return $this->items;
    }

    /**
     * Delete the contents of a given key or keys
     *
     * @param array<Key>|int|string|null  $keys
     *
     * @return $this
     */
    public function clear( $keys = null ) {
        if ( $keys === null ) {
            $this->items = [];

            return $this;
        }

        $keys = (array) $keys;

        foreach ( $keys as $key ) {
            $this->set( $key, [] );
        }

        return $this;
    }

    /**
     * Delete the given key or keys
     *
     * @param array<Key>|array<Key, Value>|int|string  $keys
     *
     * @return $this
     */
    public function delete( $keys ) : DotArray {
        $keys = (array) $keys;

        foreach ( $keys as $key ) {
            if ( $this->exists( $this->items, $key ) ) {
                unset( $this->items[ $key ] );

                continue;
            }

            $items       = &$this->items;
            $segments    = explode( $this->delimiter, $key );
            $lastSegment = array_pop( $segments );

            foreach ( $segments as $segment ) {
                if ( !isset( $items[ $segment ] ) || !is_array( $items[ $segment ] ) ) {
                    continue 2;
                }

                $items = &$items[ $segment ];
            }

            unset( $items[ $lastSegment ] );
        }

        return $this;
    }

    /**
     * Checks if the given key exists in the provided array.
     *
     * @param array<Key, Value>  $array  Array to validate
     * @param int|string         $key    The key to look for
     *
     * @return bool
     */
    protected function exists( $array, $key ) : bool {
        return array_key_exists( $key, $array );
    }

    /**
     * Flatten an array with the given character as a key delimiter
     *
     * @param string  $delimiter
     * @param mixed   $items
     * @param string  $prepend
     *
     * @return array<Key, Value>
     */
    public function flatten( $delimiter = '.', $items = null, $prepend = '' ) {
        $flatten = [];

        if ( $items === null ) {
            $items = $this->items;
        }

        foreach ( $items as $key => $value ) {
            if ( is_array( $value ) && !empty( $value ) ) {
                $flatten[] = $this->flatten( $delimiter, $value, $prepend . $key . $delimiter );
            }
            else {
                $flatten[] = [ $prepend . $key => $value ];
            }
        }

        return array_merge( ...$flatten );
    }

    /**
     * Return the value of a given key
     *
     * @param int|string|null  $key
     * @param mixed            $default
     *
     * @return mixed
     */
    public function get( $key = null, $default = null ) {
        if ( $key === null ) {
            return $this->items;
        }

        if ( $this->exists( $this->items, $key ) ) {
            return $this->items[ $key ];
        }

        if ( !is_string( $key ) || strpos( $key, $this->delimiter ) === false ) {
            return $default;
        }

        $items = $this->items;

        foreach ( explode( $this->delimiter, $key ) as $segment ) {
            if ( !is_array( $items ) || !$this->exists( $items, $segment ) ) {
                return $default;
            }

            $items = &$items[ $segment ];
        }

        return $items;
    }

    /**
     * Return the given items as an array
     *
     * @param array<Key, Value>|self<Key, Value>|object|string  $items
     *
     * @return array<Key, Value>
     */
    protected function getArrayItems( $items ) : array {
        if ( is_array( $items ) ) {
            return $items;
        }

        if ( $items instanceof DotArray ) {
            return $items->all();
        }

        return (array) $items;
    }

    /**
     * Check if a given key or keys exists
     *
     * @param array<Key>|int|string  $keys
     *
     * @return bool
     */
    public function has( $keys ) {
        $keys = (array) $keys;

        if ( !$this->items || $keys === [] ) {
            return false;
        }

        foreach ( $keys as $key ) {
            $items = $this->items;

            if ( $this->exists( $items, $key ) ) {
                continue;
            }

            foreach ( explode( $this->delimiter, $key ) as $segment ) {
                if ( !is_array( $items ) || !$this->exists( $items, $segment ) ) {
                    return false;
                }

                $items = $items[ $segment ];
            }
        }

        return true;
    }

    /**
     * Check if a given key or keys are empty
     *
     * @param array<Key>|int|string|null  $keys
     *
     * @return bool
     */
    public function isEmpty( $keys = null ) {
        if ( $keys === null ) {
            return empty( $this->items );
        }

        $keys = (array) $keys;

        foreach ( $keys as $key ) {
            if ( !empty( $this->get( $key ) ) ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Merge a given array or a Dot object with the given key
     * or with the whole Dot object
     *
     * @param array<Key, Value>|self<Key, Value>|string  $key
     * @param array<Key, Value>|self<Key, Value>         $value
     *
     * @return $this
     */
    public function merge( $key, $value = [] ) {
        if ( is_array( $key ) ) {
            $this->items = array_merge( $this->items, $key );
        }
        elseif ( is_string( $key ) ) {
            $items = (array) $this->get( $key );
            $value = array_merge( $items, $this->getArrayItems( $value ) );

            $this->set( $key, $value );
        }
        elseif ( $key instanceof self ) {
            $this->items = array_merge( $this->items, $key->all() );
        }

        return $this;
    }

    /**
     * Recursively merge a given array or a Dot object with the given key
     * or with the whole Dot object.
     *
     * Duplicate keys are converted to arrays.
     *
     * @param array<Key, Value>|self<Key, Value>|string  $key
     * @param array<Key, Value>|self<Key, Value>         $value
     *
     * @return $this
     */
    public function mergeRecursive( $key, $value = [] ) {
        if ( is_array( $key ) ) {
            $this->items = array_merge_recursive( $this->items, $key );
        }
        elseif ( is_string( $key ) ) {
            $items = (array) $this->get( $key );
            $value = array_merge_recursive( $items, $this->getArrayItems( $value ) );

            $this->set( $key, $value );
        }
        elseif ( $key instanceof self ) {
            $this->items = array_merge_recursive( $this->items, $key->all() );
        }

        return $this;
    }

    /**
     * Recursively merge a given array or a Dot object with the given key
     * or with the whole Dot object.
     *
     * Instead of converting duplicate keys to arrays, the value from
     * given array will replace the value in Dot object.
     *
     * @param array<Key, Value>|self<Key, Value>|string  $key
     * @param array<Key, Value>|self<Key, Value>         $value
     *
     * @return $this
     */
    public function mergeRecursiveDistinct( $key, $value = [] ) {
        if ( is_array( $key ) ) {
            $this->items = $this->arrayMergeRecursiveDistinct( $this->items, $key );
        }
        elseif ( is_string( $key ) ) {
            $items = (array) $this->get( $key );
            $value = $this->arrayMergeRecursiveDistinct( $items, $this->getArrayItems( $value ) );

            $this->set( $key, $value );
        }
        elseif ( $key instanceof self ) {
            $this->items = $this->arrayMergeRecursiveDistinct( $this->items, $key->all() );
        }

        return $this;
    }

    /**
     * Merges two arrays recursively. In contrast to array_merge_recursive,
     * duplicate keys are not converted to arrays but rather overwrite the
     * value in the first array with the duplicate value in the second array.
     *
     * @param array<Key, Value>|array<Key, array<Key, Value>>  $array1  Initial array to merge
     * @param array<Key, Value>|array<Key, array<Key, Value>>  $array2  Array to recursively merge
     *
     * @return array<Key, Value>|array<Key, array<Key, Value>>
     */
    protected function arrayMergeRecursiveDistinct( array $array1, array $array2 ) {
        $merged = &$array1;

        foreach ( $array2 as $key => $value ) {
            if ( is_array( $value ) && isset( $merged[ $key ] ) && is_array( $merged[ $key ] ) ) {
                $merged[ $key ] = $this->arrayMergeRecursiveDistinct( $merged[ $key ], $value );
            }
            else {
                $merged[ $key ] = $value;
            }
        }

        return $merged;
    }

    /**
     * Return the value of a given key and
     * delete the key
     *
     * @param int|string|null  $key
     * @param mixed            $default
     *
     * @return mixed
     */
    public function pull( $key = null, $default = null ) {
        if ( $key === null ) {
            $value = $this->all();
            $this->clear();

            return $value;
        }

        $value = $this->get( $key, $default );
        $this->delete( $key );

        return $value;
    }

    /**
     * Push a given value to the end of the array
     * in a given key
     *
     * @param mixed  $key
     * @param mixed  $value
     *
     * @return $this
     */
    public function push( $key, $value = null ) {
        if ( $value === null ) {
            $this->items[] = $key;

            return $this;
        }

        $items = $this->get( $key );

        if ( is_array( $items ) || $items === null ) {
            $items[] = $value;
            $this->set( $key, $items );
        }

        return $this;
    }

    /**
     * Replace all values or values within the given key
     * with an array or Dot object
     *
     * @param array<Key, Value>|self<Key, Value>|string  $key
     * @param array<Key, Value>|self<Key, Value>         $value
     *
     * @return $this
     */
    public function replace( $key, $value = [] ) {
        if ( is_array( $key ) ) {
            $this->items = array_replace( $this->items, $key );
        }
        elseif ( is_string( $key ) ) {
            $items = (array) $this->get( $key );
            $value = array_replace( $items, $this->getArrayItems( $value ) );

            $this->set( $key, $value );
        }
        elseif ( $key instanceof self ) {
            $this->items = array_replace( $this->items, $key->all() );
        }

        return $this;
    }

    /**
     * Set a given key / value pair or pairs
     *
     * @param array<Key, Value>|int|string  $keys
     * @param mixed                         $value
     *
     * @return $this
     */
    public function set( $keys, mixed $value = null ) : DotArray {
        if ( is_array( $keys ) ) {
            foreach ( $keys as $k => $v ) {
                $this->set( $k, $v );
            }

            return $this;
        }

        $items = &$this->items;

        if ( is_string( $keys ) ) {
            foreach ( explode( $this->delimiter, $keys ) as $key ) {
                if ( !isset( $items[ $key ] ) || !is_array( $items[ $key ] ) ) {
                    $items[ $key ] = [];
                }

                $items = &$items[ $key ];
            }
        }

        $items = $value;

        return $this;
    }

    /**
     * Replace all items with a given array
     *
     * @param mixed  $items
     *
     * @return $this
     */
    public function setArray( $items ) {
        $this->items = $this->getArrayItems( $items );

        return $this;
    }

    /**
     * Replace all items with a given array as a reference
     *
     * @param array<Key, Value>  $items
     *
     * @return $this
     */
    public function setReference( array &$items ) {
        $this->items = &$items;

        return $this;
    }

    /**
     * Return the value of a given key or all the values as JSON
     *
     * @param mixed  $key
     * @param int    $options
     *
     * @return string|false
     */
    public function toJson( $key = null, $options = 0 ) {
        if ( is_string( $key ) ) {
            return json_encode( $this->get( $key ), $options );
        }

        $options = $key === null ? 0 : $key;

        return json_encode( $this->items, $options );
    }

    /**
     * Output or return a parsable string representation of the
     * given array when exported by var_export()
     *
     * @param array<Key, Value>  $items
     *
     * @return object<DotArray>
     */
    public static function __set_state( array $items ) : DotArray {
        return (object) $items;
    }

    /*
     * --------------------------------------------------------------
     * ArrayAccess interface
     * --------------------------------------------------------------
     */

    /**
     * Check if a given key exists
     *
     * @param int|string  $key
     *
     * @return bool
     */
    public function offsetExists( $key ) : bool {
        return $this->has( $key );
    }

    /**
     * Return the value of a given key
     *
     * @param int|string  $key
     *
     * @return mixed<Value>
     */
    #[\ReturnTypeWillChange]
    public function offsetGet( $key ) : mixed {
        return $this->get( $key );
    }

    /**
     * Set a given value to the given key
     *
     * @param int|string|null  $key
     * @param mixed<Value>     $value
     */
    public function offsetSet( $key, mixed $value ) : void {
        if ( $key === null ) {
            $this->items[] = $value;

            return;
        }

        $this->set( $key, $value );
    }

    /**
     * Delete the given key
     *
     * @param int|string  $key
     *
     * @return void
     */
    public function offsetUnset( $key ) : void {
        $this->delete( $key );
    }

    /*
     * --------------------------------------------------------------
     * Countable interface
     * --------------------------------------------------------------
     */

    /**
     * Return the number of items in a given key
     *
     * @param int|string|null  $key
     *
     * @return int
     */
    public function count( $key = null ) : int {
        return count( $this->get( $key ) );
    }

    /*
     * --------------------------------------------------------------
     * IteratorAggregate interface
     * --------------------------------------------------------------
     */

    /**
     * Get an iterator for the stored items
     *
     * @return \ArrayIterator
     */
    public function getIterator() : \ArrayIterator {
        return new \ArrayIterator( $this->items );
    }

    /*
     * --------------------------------------------------------------
     * JsonSerializable interface
     * --------------------------------------------------------------
     */

    /**
     * Return items for JSON serialization
     *
     * @return array<Key, Value>
     */
    public function jsonSerialize() : array {
        return $this->items;
    }
}