<?php

namespace Northrook\Support\String;

use JetBrains\PhpStorm\Deprecated;
use JetBrains\PhpStorm\ExpectedValues;
use Northrook\Logger\Log;
use Northrook\Support\Str;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use function Northrook\isUrl;

/**
 * @internal
 * @author  Martin Nielsen <mn@northrook.com>
 */
trait PathFunctions
{

    /**
     * @param string       $path
     * @param null|string  $fullPath
     *
     * @return string
     * @deprecated
     */
    #[Deprecated( 'Use ' . Str::class . '::getPath instead.' )]
    public static function filepath( string $path, ?string $fullPath = null ) : string {
        trigger_deprecation( 'northrook/support', 'dev-env', "UseNorthrook/Core/normalizePath()." );
        Log::Notice(
            'Using deprecated function {old}. Use {new} instead.',
            [
                'old' => Str::class . '::filepath',
                'new' => Str::class . '::getPath',
            ],
        );
        return Str::getPath( $path );
    }

    #[Deprecated]
    public static function parameterDirname(
        string  $path = '%kernel.project_dir%',
        #[ExpectedValues( [ 'log', 'error', 'exception' ] )]
        ?string $onInvalidPath = 'exception',
    ) : ?string {

        trigger_deprecation(
            'northrook/string',
            '1.0.0',
            'The method "%s" is deprecated. Use Symfony/Finder instead, or refactor into native function in core/functions.php',
            __METHOD__,
        );

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

    /**
     * @param string       $path
     * @param bool         $create
     * @param null|string  $fallback
     *
     * @return null|string
     * @deprecated
     */
    public static function getPath( string $path, bool $create = false, ?string $fallback = null ) : ?string {
        trigger_deprecation(
            'northrook/support', 'dev-env',
            "No current replacement. Consider allowing Northrook/Filesystem/exists() to perform mkdir.",
        );
        return \Northrook\Filesystem\File::exists( $path ) ? $path : $fallback;
    }

    /**
     * @param string  $path
     * @param bool    $create
     *
     * @return string
     * @deprecated
     */
    public static function getDirectoryPath( string $path, bool $create = false ) : string {
        trigger_deprecation( 'northrook/support', 'dev-env', "Use Northrook/Filesystem/File instead?" );
        $path = substr( $path, 0, (int) strrpos( Str::getPath( $path, $create ), DIRECTORY_SEPARATOR ) );

        return rtrim( $path, DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR;
    }

    public static function isPathTraversable( string $path ) : bool {
        return Str::contains( $path, [ ".", "../", ".." . DIRECTORY_SEPARATOR ] );
    }


    /**
     * Determine if a given value is a valid URL.
     *
     * @param mixed  $value
     *
     * @return bool
     * @deprecated
     */
    public static function isUrl( mixed $value ) : bool {
        trigger_deprecation( 'northrook/support', 'dev-env', "Use Northrook/Core/isUrl() instead." );
        return isUrl( $value );
    }

    /**
     *  Normalise a `string`, assuming it is a `path`.
     *
     *  - Removes repeated slashes.
     *  - Normalises slashes to system separator.
     *  - Prevents backtracking.
     *  - No validation is performed.
     *  - This function is `Memoized`.
     *
     * Options:
     *  - Set `$allowFilePath` to `false` will throw an exception if the path has a file extension.
     *  - Set `$trailingSlash` to `false` will remove the trailing slash for directories.
     *
     *
     * @param ?string  $string
     * @param bool     $allowFilePath
     * @param bool     $trailingSlash  Append a trailing slash.
     *
     * @return ?string
     */
    public static function normalizePath(
        ?string $string,
        bool    $allowFilePath = true,
        bool    $trailingSlash = false,
    ) : ?string {
        trigger_deprecation( 'northrook/support', 'dev-env', "Use Northrook/Core/normalizePath() instead." );
        return \Northrook\Core\normalizePath( $string, $trailingSlash );
    }

    public static function normalizeRealPath( string $path ) : string {
        trigger_deprecation( 'northrook/support', 'dev-env', 'Use Northrook/Core/normalizePath() instead.' );
        return \Northrook\Core\normalizePath( $path );
    }

    /**
     * @param ?string  $string
     * @param bool     $trailingSlash
     *
     * @return ?string
     *
     * @link https://github.com/glenscott/url-normalizer/blob/master/src/URL/Normalizer.php Good starting point
     */
    public static function normalizeURL(
        ?string $string,
        bool    $trailingSlash = true,
    ) : ?string {
        trigger_deprecation( 'northrook/support', 'dev-env', 'Use Northrook/Core/normalizeUrl() instead.' );
        return \Northrook\Core\normalizeUrl( $string );
    }

    public static function href( string $string ) : string {
        trigger_deprecation( 'northrook/support', 'dev-env', 'Use northrook/content-formatter instead.' );

        $string = strtolower( trim( $string ) );

        if ( filter_var( $string, FILTER_VALIDATE_EMAIL ) ) {
            $string = Str::start( $string, 'mailto:' );
        }

        return $string;
    }

    #[Deprecated( 'Use Pathfinder' )]
    public static function url( ?string $string, bool $absolute = false, bool $trailing = false ) : ?string {

        trigger_deprecation( 'northrook/support', 'dev-env', "Use Northrook/Pathfinder/Path instead." );

        $url = filter_var( $string, FILTER_SANITIZE_URL );

        $url = trim( $url, '/' );

        if ( $trailing ) {
            $url = rtrim( $url, '/' );
        }

        if ( !$absolute ) {
            $url = '/' . $url;
        }
        else {
            $url = $_SERVER[ 'SERVER_NAME' ] . '/' . $url;
        }

        return $url;
    }


}