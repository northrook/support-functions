<?php

declare( strict_types = 1 );

namespace Northrook\src\Internal\Cache;

/**
 * @internal
 *
 * @author  Martin Nielsen <mn@northrook.com>
 */
trait MemoizationCache
{
    /**
     * @var array<string, array{value: mixed, hit: int, returnType: string}>
     */
    protected static array $memoCache = [];

    public static function debugMemoizationCache() : array {
        return MemoizationCacheStore::getMemoizationCacheArray();
    }

    /**
     * Memoize the return value of a function.
     *
     * - `$arguments` are hashed and as the key to the cache.
     * - The `$return` function is called with the `$arguments`, and stored in the cache.
     *
     * @param callable  $return
     * @param mixed     $arguments
     *
     * @return mixed
     */
    protected static function memoize( callable $return, mixed ...$arguments ) : mixed {

        // Generate a unique key from provided arguments.
        $key = hash( 'xxh3', json_encode( $arguments ) ?: serialize( $arguments ) );

        $cacheHit = MemoizationCacheStore::get( $key );

        if ( $cacheHit ) {
            return $cacheHit;
        }

        return MemoizationCacheStore::set( $key, $return, $arguments, static::class );


        // If the key is already in the cache, increment the hit count and return the value.
        if ( isset( static::$memoCache[ $key ] ) ) {
            static::$memoCache[ $key ][ 'hit' ]++;
            return static::$memoCache[ $key ][ 'value' ];
        }

        // Call the function with provided arguments.
        $value = $return( ...$arguments );
        $type  = gettype( $value );

        // If the return value is empty, return it.
        if ( !$value && $type !== "boolean" ) {
            return $value;
        }

        // Store the return value in the cache.
        static::$memoCache[ $key ] = [
            'value'      => $value,
            'hit'        => 1,
            'returnType' => gettype( $value ),
        ];

        // Return the return value.
        return $value;

        // Short version without hit counter or return type:
        // return self::$memoCache[ $key ] ??= self::$memoCache[ $key ] = $return( ...$arguments );
    }

    /**
     * Return the {@see static::$memoCache} array.
     *
     * @return array
     */
    protected static function getMemoizationCacheArray() : array {
        return static::$memoCache;
    }

    /**
     * Clear the {@see static::$memoCache}.
     *
     * @return void
     */
    protected static function clearMemoizationCache() : void {
        static::$memoCache = [];
    }
}