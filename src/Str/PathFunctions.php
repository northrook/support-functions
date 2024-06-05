<?php

namespace Northrook\Support\Str;

use JetBrains\PhpStorm\Deprecated;
use JetBrains\PhpStorm\ExpectedValues;
use Northrook\Logger\Log;
use Northrook\Support\File;
use Northrook\Support\Str;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

trait PathFunctions
{
    public static function parameterDirname(
        string  $path = '%kernel.project_dir%',
        #[ExpectedValues( [ 'log', 'error', 'exception' ] )]
        ?string $onInvalidPath = 'exception',
    ) : ?string {

        if ( false === str_starts_with( $path, '../' ) ) {
            return static::normalizePath( $path );
        }

        $level = substr_count( $path, '../', 0, strrpos( $path, '../' ) + 3 );
        $root  = dirname( debug_backtrace()[ 0 ][ 'file' ], $level ?: 1 );
        $path  = $root . '/' . substr( $path, strrpos( $path, '../' ) + 3 );

        $path = static::normalizePath( $path );

        if ( file_exists( $path ) ) {
            return $path;
        }

        match ( $onInvalidPath ) {
            'exception' => throw new FileNotFoundException( $path ),
            'error'     => trigger_error( "File \"$path\" does not exist.", E_USER_ERROR ),
            'log'       => Log::Error(
                message : 'File {path} does not exist.',
                context : [ 'path' => $path, 'file' => debug_backtrace()[ 0 ][ 'file' ] ],
            ),
            default     => null,
        };

        return $path;
    }

    #[Deprecated( 'Use ' . Str::class . '::getPath instead.' )]
    public static function filepath( string $path, ?string $fullPath = null ) : string {
        Log::Notice(
            'Using deprecated function {old}. Use {new} instead.',
            [
                'old' => Str::class . '::filepath',
                'new' => Str::class . '::getPath',
            ],
        );
        return Str::getPath( $path );
    }

    public static function getPath( string $path, bool $create = false, ?string $fallback = null ) : ?string {
        if ( $create ) {
            return File::mkdir( $path );
        }

        $path = Str::normalizePath( string : $path );

        return File::exists( $path ) ? $path : $fallback;
    }

    public static function getDirectoryPath( string $path ) : string {
        $path = substr( $path, 0, (int) strrpos( Str::filepath( $path ), DIRECTORY_SEPARATOR ) );

        return rtrim( $path, DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR;
    }

    public static function isPathTraversable( string $path ) : bool {
        return Str::contains( $path, [ ".", "../", ".." . DIRECTORY_SEPARATOR ] );
    }

    /**
     * Normalise a `string`, assuming it is a `path`.
     *
     * - Removes repeated slashes.
     * - Normalises slashes to system separator.
     * - Prevents backtracking.
     * - Optional trailing slash for directories.
     * - No validation is performed.
     *
     * @param string   $string         The string to normalize.
     * @param ?string  $append         Optional appended string to append.
     * @param bool     $trailingSlash  Whether to append a trailing slash to the path.
     *
     * @return string  The normalized path.
     */
    public static function normalizePath(
        string  $string,
        ?string $append = null,
        bool    $trailingSlash = true,
    ) : string {

        if ( $append ) {
            $string .= "/$append";
        }

        $string = mb_strtolower( str_replace( "\\", "/", $string ) );

        if ( str_contains( $string, '/' ) ) {

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
        }
        else {
            $path = $string;
        }

        // If the string contains a valid extension, return it as-is
        if ( isset( pathinfo( $path )[ 'extension' ] ) && !str_contains( pathinfo( $path )[ 'extension' ], '%' ) ) {
            return $path;
        }

        return $trailingSlash ? $path . DIRECTORY_SEPARATOR : $path;
    }


    /**
     * @param string   $string
     * @param ?string  $requireScheme  = ['http', 'https', 'ftp', 'ftps', 'mailto','file','data']
     * @param bool     $trailingSlash
     *
     * @return ?string
     *
     * @link https://github.com/glenscott/url-normalizer/blob/master/src/URL/Normalizer.php Good starting point
     */
    public static function normalizeURL(
        string  $string,
        ?string $requireScheme = null,
        bool    $trailingSlash = true,
    ) : ?string {

        [ $url, $query ] = explode( '?', $string, 2 );

        return $trailingSlash ? rtrim( $string, '/' ) : $string;
    }
}