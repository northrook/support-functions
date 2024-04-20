<?php

namespace Northrook\Support\Functions;

use Northrook\Support\Config;
use Northrook\Support\Str;

trait PathFunctions
{

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
        // Bail if the $source is null, empty, or does not contain a scheme
        if ( !$string || false === str_contains( $string, '://' ) ) {
            return false;
        }

        $url = parse_url( $string );


        if ( false === $url || Config::security()->scheme !== $url[ 'scheme' ] ) {
            return false;
        }

        if ( $validate ) {

            $url = filter_var( $string, FILTER_VALIDATE_URL );

            if ( false === $url ) {
                return false;
            }

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

    public static function filepath( string $path, ?string $fullPath = null ) : string {

        $path = self::normalizePath( $path );

        if ( $fullPath ) {
            $fullPath = self::normalizePath( $fullPath );
        }

        return str_replace(
            '\\\\', '\\', $fullPath ? Str::start(
            string : $path, with : Str::end(
            string : $fullPath,
            with   : DIRECTORY_SEPARATOR,
        ),
        ) : $path,
        );
    }

    /**
     * @param string  $string
     *
     * @return string
     */
    public static function normalizePath( string $string ) : string {

        $string = mb_strtolower( strtr( $string, "\\", "/" ) );

        if ( str_contains( $string, '/' ) === false ) {
            return $string;
        }

        $path = [];

        foreach ( array_filter( explode( '/', $string ) ) as $part ) {
            if ( $part === '..' && $path && end( $path ) !== '..' ) {
                array_pop( $path );
            }
            elseif ( $part !== '.' ) {
                $path[] = trim( $part );
            }
        }

        $path = implode(
            separator : DIRECTORY_SEPARATOR,
            array     : $path,
        );

        if ( false === isset( pathinfo( $path )[ 'extension' ] ) ) {
            $path .= DIRECTORY_SEPARATOR;
        }

        return $path;
    }

}