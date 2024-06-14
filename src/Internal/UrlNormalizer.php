<?php

namespace Northrook\src\Internal;

require 'iana-uri-schemes.php';

/**
 * @internal
 * @author  Martin Nielsen <mn@northrook.com>
 *
 *
 * @link    https://github.com/glenscott/url-normalizer/blob/master/src/URL/Normalizer.php Good starting point
 */
final class UrlNormalizer
{
    public function __construct(
        private string  $url,
        private ?string $requireScheme = null,
    ) {
        dump( IANA_URI_SCHEMES );
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
}