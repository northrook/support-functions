<?php

namespace Northrook\Support;

// TODO: https://www.php.net/manual/en/class.numberformatter.php

use JetBrains\PhpStorm\ExpectedValues;
use Parsedown;

class Format
{
    // private static function stringLength( string $string, int $min, int $max, string $action = 'warn' ) : string {
    //
    // }

    public static function title(
        string $string,
        #[ExpectedValues( values : [ 'document', 'paragraph', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6' ] )]
        string $type,
    ) : string {

        $string = trim($string);

        return match ( $type ) {
            'document' => Filter::string( $string),
            'paragraph' => ucfirst($string), // Max length in accordance with max recommended paragraph length
            'h1' => ucfirst($string), // Length according to primary heading length
            'h2' => ucfirst($string), // Length according to heading length
            'h3', 'h4', 'h5', 'h6' => strtoupper( $string),
            default => Filter::string( $string)
        };
    }

    public static function url( string $string ) : string {

        $schema = null;

        if ( str_starts_with( $string, 'http://' ) || str_starts_with( $string, 'https://' ) ) {
            $schema = parse_url( $string, PHP_URL_SCHEME );
            $string = substr( $string, strpos( $string, '://' ) + 3 );
        }

        $parts = explode( '/', $string );

        foreach ( $parts as $i => $part ) {
            $part[ $i ] = '<wbr><span>/</span>' . $part;
        }

        $string = $schema . implode( '/', $parts );


        // take out http(s)://
        // split each by /
        // wrap / and schema in <spa>
        // implode with <wbr> before /

        return '<span class="url">' . $string . '</span>';
    }

    public static function markdown( string $string ) : string {
        return ( new Parsedown() )->text( $string );
    }

    /**
     * * Spacing, XX XX XX XX, XXXX XXX XXX etc
     * * Areacode detector, 00XX, +XX etc
     *
     * @param string  $number
     *
     * @return string
     */
    public static function telephone( string $number ) : string {
        return preg_replace( '/[^0-9]/', '', $number );
    }

    public static function quotes( string $string, array $options = [] ) : string {
        // ‘ ’ &lsquo; &rsquo;
        // ‚ &bdquo; <- NOT A COMMA
        // “ ” 	&ldquo; &rdquo;
        // ′ ″ 	&prime; &Prime;
        // ' " `

        /// RULES
        // - Must not be within an HTML tag
        // - Must not be within an HTML attribute

        $options = array_merge(
            [
                'quotes'     => '"',
                'openQuote'  => '«',
                'closeQuote' => '»',
            ],
            $options,
        );

        return preg_replace( '/"/', '&quot;', $string );
    }

    public static function nl2span( string $string, string $whitespace = " " ) : string {
        $string = str_replace( [ '<p>', '</p>' ], [ '<span>', '</span>' ], $string );
        $array  = Arr::explode( PHP_EOL, $string );

        return Arr::implode( $array, wrap : 'span' );
    }

    public static function nl2p( ?string $string, string $whitespace = " " ) : ?string {

        if ( !$string ) {
            return null;
        }

        $explode = Arr::explode( "\n", $string );

        return Arr::implode( $explode, wrap : 'p' );
    }

    public static function nl2Auto( ?string $string, string $whitespace = " " ) : ?string {

        if ( !$string ) {
            return null;
        }

        $array = Arr::explode( "\n", $string );

        if ( empty( $array ) ) {
            return null;
        }

        if ( count( $array ) === 1 ) {
            return "<span>$string</span>";
        }

        return Arr::implode( $array, wrap : 'p' );
    }
}