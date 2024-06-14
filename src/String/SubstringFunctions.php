<?php

namespace Northrook\Support\String;

use JetBrains\PhpStorm\Deprecated;
use Northrook\Support\Str;

/**
 * Splitting and manipulating substrings.
 */
trait SubstringFunctions
{


    /**
     * Determine if a $string contains all $substrings.
     *
     *  * Case Insensitive by default
     *
     *
     * @param  ?string         $string
     * @param iterable|string  $all
     * @param bool             $caseSensitive
     *
     * @return bool
     */
    #[Deprecated( 'Use containsAll()' )]
    public static function containsAll( ?string $string, iterable | string $all, bool $caseSensitive = false ) : bool {
        trigger_deprecation(
            'northrook/string',
            '1.0.0',
            'The method "%s" is deprecated. Use containsAll() instead.',
            __METHOD__,
        );
        return Str::contains(
                            $string,
                            $all,
            containsAll   : true,
            caseSensitive : $caseSensitive,
        );
    }

    // TODO : Validate
    public static function contains(
        string         $string,
        string | array $needle,
        bool           $returnNeedles = false,
        bool           $containsOnlyOne = false,
        bool           $containsAll = false,
        bool           $caseSensitive = false,
    ) : bool | int | array | string {

        $count    = 0;
        $contains = [];

        $find = static fn ( string $string ) => $caseSensitive ? $string : strtolower( $string );

        $string = $find( $string );

        if ( is_string( $needle ) ) {
            $count = substr_count( $string, $find( $needle ) );
        }
        else {
            foreach ( $needle as $index => $value ) {
                $match = substr_count( $string, $find( $value ) );
                if ( $match ) {
                    $contains[] = $value;
                    $count      += $match;
                    unset( $needle[ $index ] );
                }
            }
        }

        if ( $containsOnlyOne && count( $contains ) !== 1 ) {
            return false;
        }

        if ( $containsAll && empty( $needle ) ) {
            return true;
        }

        if ( $returnNeedles ) {
            return ( count( $needle ) === 1 ) ? $needle[ 0 ] : $needle;
        }

        return $count;
    }

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

    /** Determine if a $string starts with any $substrings.
     *
     * * Case Insensitive by default
     *
     * @param  ?string      $string
     * @param string|array  $substrings
     * @param bool          $caseSensitive
     *
     * @return bool
     */
    public static function startsWith(
        ?string        $string,
        string | array $substrings,
        bool           $caseSensitive = false,
    ) : bool {

        if ( !$string ) {
            return false;
        }

        $string = $caseSensitive ? $string : mb_strtolower( $string );

        foreach ( (array) $substrings as $substring ) {
            if ( str_starts_with(
                $string,
                $caseSensitive ? $substring : mb_strtolower( $substring ),
            ) ) {
                return true;
            }
        }

        return false;
    }

    /** Determine if a $string ends with any $substrings.
     *
     * * Case Insensitive by default
     *
     * @param  ?string      $string
     * @param string|array  $substrings
     * @param bool          $caseSensitive
     *
     * @return bool
     */
    public static function endsWith(
        ?string        $string,
        string | array $substrings,
        bool           $caseSensitive = false,
    ) : bool {

        if ( !$string ) {
            return false;
        }

        $string = $caseSensitive ? $string : mb_strtolower( $string );

        foreach ( (array) $substrings as $substring ) {
            if ( str_ends_with(
                $string,
                $caseSensitive ? $substring : mb_strtolower( $substring ),
            ) ) {
                return true;
            }
        }

        return false;
    }

    /** Returns a $string that starts $with a certain substring.
     *
     * * Passed null values will be stringified before comparison
     *
     * @param  ?string  $string  The original string
     * @param  ?string  $with    The desired starting substring
     * @param bool      $trim    Determines if the strings should be trimmed before parsing
     *
     * @return string  The processed string
     */
    public static function start( ?string $string, ?string $with, bool $trim = false ) : string {

        $string = $trim ? trim( $string ) : (string) $string;

        if ( $with === null || str_starts_with( $string, $with ) ) {
            return $string;
        }

        $with = $trim ? trim( $with ) : $with;

        return $with . $string;
    }

    /** Returns a $string that ends $with a certain substring.
     *
     * * Passed null values will be stringified before comparison
     * * Avoid `$trim` when used in loops
     *
     * @param  ?string  $string  The original string
     * @param  ?string  $with    The desired ending substring
     * @param bool      $trim    Determines if the strings should be trimmed before parsing
     *
     * @return string  The processed string
     */
    public static function end( ?string $string, ?string $with, bool $trim = false ) : string {

        $string = $trim ? trim( $string ) : (string) $string;

        if ( $with === null || str_ends_with( $string, $with ) ) {
            return $string;
        }

        $with = $trim ? trim( $with ) : $with;

        return $string . $with;
    }

    /**
     * Split a string by the given separator, with flexible return options.
     *
     *
     * @param  ?string  $string
     * @param string    $return     = ['array', 'first', 'last'][any]
     * @param string    $separator  = ':'
     *
     * @return array   | string | null
     */
    public static function split(
        ?string $string,
        string  $return = 'array',
        string  $separator = ':',
    ) : array | string | null {

        // TODO: Refactor using Str::before, to allow for nth occurrence

        $array = array_filter( explode( $separator, $string ) );

        if ( !$array ) {
            return null;
        }

        if ( $return === 'first' ) {
            return array_shift( $array ) ?: null;
        }

        if ( $return === 'last' ) {
            return array_pop( $array ) ?: null;
        }

        return $array;
    }
}