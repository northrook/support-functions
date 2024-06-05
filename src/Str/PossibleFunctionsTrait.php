<?php

namespace Northrook\Support\Str;

use JetBrains\PhpStorm\Pure;
use Northrook\Types\Path;

trait PossibleFunctionsTrait
{


    /**
     * From Core\Support\Str
     *
     * @param string|Path  $path
     * @param null|string  $scheme  'http' | 'https' | 'ftp' | 'ftps' | 'file' | null as any
     *
     * @return bool
     */
    public static function _isURL( string | Path $path, ?string $scheme = 'https' ) : bool {

        if ( $scheme && !str_starts_with( $path, "$scheme://" ) ) {
            return false;
        }
        if ( !( str_contains( $path, "//" ) && str_contains( $path, '.' ) ) ) {
            return false;
        }

        return (bool) filter_var( $path, FILTER_VALIDATE_URL );
    }

    /** Check if a string is a valid URL
     *
     * @param null|string  $string    $string    The string to check
     * @param null|string  $scheme    The scheme to check against
     *
     * @param bool         $validate  Validate the URL
     *
     * @return bool
     * @link https://stackoverflow.com/a/68254092
     */
    public static function isUrl( ?string $string, ?string $scheme = null, bool $validate = false ) : bool {

        // Validate defined scheme
        if ( $scheme && !str_starts_with( $string, $scheme . '://' ) ) {
            return false;
        }

        // A URL must contain a scheme, and a host somewhere
        if ( !( str_contains( $string, "//" ) && str_contains( $string, '.' ) ) ) {
            return false;
        };

        $url = filter_var( $string, FILTER_VALIDATE_URL );

        // Schema validating from Config: Config::security()->scheme !== $url[ 'scheme' ]

        if ( false === $url ) {
            return false;
        }

        if ( $validate ) {

            $headers = get_headers( $string );

            if ( false === $headers ) {
                return false;
            }

            if ( false === str_contains( $headers[ 0 ], '200' ) ) {
                return false;
            }
        }

        return true;


    }

    // Look into what we want to achieve when sanitizing a string
    #[Pure]
    public static function sanitize( ?string $string, bool $stripTags = false ) : string {
        if ( $stripTags ) {
            $string = strip_tags( $string );
        }
        return htmlspecialchars( (string) $string, ENT_QUOTES | ENT_HTML5 | ENT_SUBSTITUTE, 'UTF-8' );
    }
}