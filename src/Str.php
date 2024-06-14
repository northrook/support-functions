<?php

namespace Northrook\Support;

use JetBrains\PhpStorm\ExpectedValues;
use JetBrains\PhpStorm\Pure;
use JsonException;
use Northrook\src\String\{BooleanFunctions, PathFunctions, SubstringFunctions, TrimFunctions, ValueConversionFunctions};

/**
 * @author  Martin Nielsen <mn@northrook.com>
 */
final class Str
{
    use  SubstringFunctions, TrimFunctions, PathFunctions, ValueConversionFunctions, BooleanFunctions;

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

        $key = static function (
            string  $string,
            string  $separator,
            ?string $preserve,
            string  $case,
            ?string $asciiLanguage,
        ) {
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

        };

        return Cached( $key, [ $string, $separator, $preserve, $case, $asciiLanguage ] );

        // return static::memoize( $key, $string, $separator, $preserve, $case, $asciiLanguage );
    }

    /**
     * @param null|string  $string
     * @param bool         $stripTags
     *
     * @return string
     */
    #[Pure]
    public static function sanitize( ?string $string, bool $stripTags = false ) : string {
        return htmlspecialchars(
            string   : $stripTags ? strip_tags( $string ) : $string,
            flags    : ENT_QUOTES | ENT_HTML5 | ENT_SUBSTITUTE,
            encoding : 'UTF-8',
        );
    }

    /**
     * Generate a deterministic hash key from a value.
     *
     * - `$value` will be stringified using `json_encode()` by default.
     * - `serialize()` will be used if `$forceSerialize` option is true, or `json_encode()` fails.
     * - The value is then hashed using `xxh3`.
     * - The hash is not reversible.
     *
     * @param mixed  $value
     * @param bool   $forceSerialize
     *
     * @return string
     */
    public static function hashKey(
        mixed $value,
        bool  $forceSerialize = false,
    ) : string {

        $data = $forceSerialize ? serialize( $value ) : json_encode( $value );

        return hash( algo : 'xxh3', data : $data ?: serialize( $value ) );
    }

    /**
     * Extract acronym from a $string
     *
     *
     * @param  ?string  $string      The string to process
     * @param bool      $capitalize  Defaults to true
     * @param string    $separator   Defaults to single whitespace
     *
     * @return ?string Acronym, or null if $string is null
     */
    public static function acronym( ?string $string, bool $capitalize = true, string $separator = ' ' ) : ?string {
        if ( !$string ) {
            return null;
        }

        $acronym = static function ( $string, $capitalize, $separator ) {


            $acronyms = array_map(
                static fn ( string $name ) => mb_substr( $name, 0, 1 ),
                explode( $separator, $string ),
            );
            $acronyms = implode( '', $acronyms );

            return $capitalize
                ? strtoupper( $acronyms )
                : $acronyms;
        };

        return Cached( $acronym, [ $string, $capitalize, $separator ] );
        // return static::memoize( $acronym, $string, $separator, $capitalize );
    }

    public static function replace(
        ?string $search,
        string  $replace,
        ?string $subject,
        ?int    $limit = null,
        bool    $caseSensitive = true,
    ) : ?string {

        if ( !$search || !$subject || 0 === $limit ) {
            return $subject;
        }

        if ( null === $limit ) {
            return $caseSensitive
                ? str_replace( $search, $replace, $subject )
                : str_ireplace( $search, $replace, $subject );
        }

        $match = mb_stripos( $subject, $search );

        for ( $i = 0; $i < $limit; $i++ ) {

            if ( false === $match ) {
                return $subject;
            }

            $subject = substr_replace( $subject, $replace, $match, strlen( $search ) );
            $match   = stripos( $subject, $search, $match + strlen( $replace ) );
        }

        return $subject;
    }

    /** Replace each key from `$map` with its value, when found in `$content`.
     *
     * @param array         $map  search:replace
     * @param string|array  $content
     * @param bool          $caseSensitive
     *
     * @return array|string|string[] The processed `$content`, or null if `$content` is empty
     */
    public static function replaceEach(
        array          $map,
        string | array $content,
        bool           $caseSensitive = true,
    ) : string | array {

        if ( !$content ) {
            return $content;
        }

        $keys = array_keys( $map );

        return $caseSensitive
            ? str_replace( $keys, $map, $content )
            : str_ireplace( $keys, $map, $content );
    }

    /**
     * Wrap the string with the given strings.
     *
     *  If $before and $after are the same, $before will be used.
     *  If $before is a `<tag>`, $after will be used as the closing `</tag>`.
     *  * Supports tag attributes.
     *
     *
     * @param  ?string     $value
     * @param string       $before
     * @param string|null  $after
     *
     * @return string
     */
    public static function wrap( ?string $value, string $before, ?string $after = null ) : string {
        if ( !$after && str_starts_with( $before, '<' ) ) {
            $tag   = strstr( $before, ' ', true ) ?: $before;
            $after = str_replace( '<', '</', rtrim( $tag, '>' ) . '>' );
        }
        else {
            $after ??= $before;
        }

        return $before . $value . $after;
    }

    public static function guessDelimiter( ?string $string ) : string {
        return Str::contains( $string, [ ' ', '-', '_', '/', '\\', ':', ';' ] );
    }

    public static function toCamel( ?string $string ) : ?string {
        $delimiter = Str::guessDelimiter( $string ) ?? ' ';
        $string    = mb_strtolower( $string );
        $camel     = [];
        $each      = explode( $delimiter, $string );

        if ( !$each ) {
            return $string;
        }

        foreach ( $each as $index => $segment ) {
            if ( $index === 0 ) {
                $camel[] = $segment;
            }
            else {
                $camel[] = ucfirst( $segment );
            }

        }

        return implode( '', $camel );
    }

    public static function asJson( mixed $value ) : string | false {
        try {
            return json_encode( $value, JSON_THROW_ON_ERROR | JSON_FORCE_OBJECT );
        }
        catch ( JsonException ) {
            return false;
        }
    }
}