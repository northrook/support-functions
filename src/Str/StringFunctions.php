<?php

namespace Northrook\Support\Str;

use JetBrains\PhpStorm\ExpectedValues;

trait StringFunctions
{
    public const FIRST = 0;
    public const LAST  = -1;
    
    /**
     * @param string[]     $string
     * @param string       $separator
     * @param null|string  $case
     *
     * @return string
     */
    public static function key(
        string | array $string,
        string         $separator = '-',
        ?string        $preserve = null,
        #[ExpectedValues( values : [
            null,
            'strtoupper',
            'strtolower',
            // 'camel',
            // 'snake'
        ] )]
        ?string        $case = 'strtolower',
        #[ExpectedValues( valuesFromClass : '\voku\helper\ASCII' )]
        ?string        $asciiLanguage = null,
    ) : string {

        $string = is_array( $string ) ? implode( $separator, $string ) : $string;

        if ( $asciiLanguage ) {
            if ( !class_exists( '\voku\helper\ASCII' ) ) {
                throw new \LogicException(
                    'The voku\helper\ASCII class is not available. Please install the voku/portable-ascii package.',
                );
            }
            $string = \voku\helper\ASCII::to_ascii( $string, $asciiLanguage );
        }
        else {
            $string = preg_replace( "/[^A-Za-z0-9_\-{$preserve}]/", "-", $string );
        }

        $string = preg_replace( "/[^A-Za-z0-9$separator$preserve]/", $separator, $string );
        $string = implode( $separator, array_filter( explode( $separator, $string ) ) );

        return match ( $case ) {
            'strtoupper' => strtoupper( $string ),
            'strtolower' => strtolower( $string ),
            // 'camel'      => Str::camel( $string ),
            // 'snake'      => Str::snake( $string ),
            default      => $string,
        };
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

}