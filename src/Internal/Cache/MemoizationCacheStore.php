<?php

namespace Northrook\src\Internal\Cache;

use Northrook\Core\Trait\StaticClass;
use Northrook\src\Time;

/**
 * @internal
 *
 * @author  Martin Nielsen <mn@northrook.com>
 */
final class MemoizationCacheStore
{
    use StaticClass;

    private static array $cache = [];

    public static function has( string $key ) : bool {
        return isset( static::$cache[ $key ] );
    }

    public static function get( string $key ) : mixed {

        if ( isset( static::$cache[ $key ] ) ) {
            static::$cache[ $key ][ 'hit' ]++;
            return static::$cache[ $key ];
        }

        return false;
    }

    /**
     * @param string        $key
     * @param callable      $return
     * @param array         $arguments
     * @param class-string  $class
     *
     * @return mixed
     */
    public static function set( string $key, callable $return, array $arguments, string $class ) : mixed {
        static::$cache[ $key ] = static::cacheItem( $return, $arguments, $class );

        return static::$cache[ $key ][ 'value' ];
    }

    public static function getMemoizationCacheArray() : array {
        return static::$cache;
    }

    private static function cacheItem( callable $return, array $arguments, string $class ) : array {
        // Call the function with provided arguments.

        $timer = Time::stopwatch();

        $value = $return( ...$arguments );


        $type = gettype( $value );
        return [
            'value'      => $value,
            'hit'        => 1,
            'returnType' => gettype( $value ),
            'caller'     => $class,
            'time'       => Time::stopwatch( $timer ),
        ];
    }
}