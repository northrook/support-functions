<?php

namespace Northrook\Support;

use JetBrains\PhpStorm\Deprecated;
use LogicException;
use Northrook\Logger\Log;
use Northrook\Support\File\MimeTypeTrait;
use Northrook\Support\Str\PathFunctions;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

/**
 * @template PathString as string
 * @template UnixTimestamp as int
 * @template Bytes as int
 */
final class File
{
    use MimeTypeTrait, PathFunctions;

    private static array $readCache = [];


    /**
     *
     * @param string  $path
     * @param int     $permissions
     *
     * @return bool|string|array
     */
    #[Deprecated( 'Use ' . File::class . '::mkdir() instead.' )]
    public static function makeDirectory(
        string $path,
        int    $permissions = 0755,
    ) : bool | string | array {
        return File::mkdir( $path, $permissions );
    }

    // todo : Implement a force-override option into static::save()
    #[Deprecated( 'Use ' . File::class . '::save instead. Better support for larger files, and streamed resources.' )]
    public static function putContents( ?string $content, string $filename, int $flags = 0, bool $override = true,
    ) : false | int {
        Log::Notice(
            'Using deprecated function {old}. Use {new} instead.',
            [
                'old' => File::class . '::putContents',
                'new' => File::class . '::save',
            ],
        );
        return File::save( $filename, $content );
    }


    #[Deprecated( 'Use ' . File::class . '::read instead.' )]
    public static function getContents( string $file, bool $cache = true ) : ?string {
        Log::Notice(
            'Using deprecated function {old}. Use {new} instead.',
            [
                'old' => File::class . '::getContents',
                'new' => File::class . '::read',
            ],
        );
        return File::read( $file, $cache );
    }

    /**
     * Checks the existence of files or directories.
     *
     * @param string<PathString>|iterable  $files  The files to check
     *
     * @return bool
     */
    public static function exists( string | iterable $files ) : bool {
        return ( new Filesystem() )->exists( $files );
    }

    /**
     * Reads the contents of a file.
     *
     * - {@see IOException} will be caught and logged as an error, returning false
     *
     * @param string<PathString>  $path  The path to the file
     *
     * @return ?string Returns the contents of the file, or null if an {@see IOException} was thrown
     *
     */
    public static function read( string $path, bool $cache = true, bool $cacheOnError = true ) : ?string {

        try {
            $contents = ( new Filesystem() )->readFile( $path );
        }
        catch ( IOException $e ) {
            $contents = null;
            Log::Error( message : $e->getMessage(), context : [ 'exception' => $e ] );
        }

        return $contents;
    }


    /**
     * Atomically dumps content into a file.
     *
     * - {@see IOException} will be caught and logged as an error, returning false
     *
     * @param string<PathString>  $path     The path to the file
     * @param string|resource     $content  The data to write into the file
     *
     * @return bool  True if the file was written to, false if it already existed or an error occurred
     *
     *
     */
    public static function save( string $path, mixed $content ) : bool {
        try {
            ( new Filesystem() )->dumpFile( $path, $content );
            return true;
        }
        catch ( IOException $e ) {
            Log::Error( message : $e->getMessage(), context : [ 'exception' => $e ] );
        }

        return false;
    }

    /**
     * Copies a file.
     *
     * If the target file is older than the origin file, it's always overwritten.
     * If the target file is newer, it is overwritten only when the
     * $overwriteNewerFiles option is set to true.
     *
     */
    public static function copy( string $originFile, string $targetFile, bool $overwriteNewerFiles = false ) : bool {
        try {
            ( new Filesystem() )->copy( $originFile, $targetFile, $overwriteNewerFiles );
            return true;
        }
        catch ( FileNotFoundException | IOException $e ) {
            Log::Error( message : $e->getMessage(), context : [ 'exception' => $e ] );
        }

        return false;
    }

    /**
     * Renames a file or a directory.
     */
    public static function rename( string $origin, string $target, bool $overwrite = false ) : bool {
        try {
            ( new Filesystem() )->rename( $origin, $target, $overwrite );
            return true;
        }
        catch ( IOException $e ) {
            Log::Error( message : $e->getMessage(), context : [ 'exception' => $e ] );
        }
        return false;
    }


    /**
     * Sets access and modification time of file.
     *
     * @param string<PathString>|iterable  $files  The files to touch
     * @param ?int<UnixTimestamp>          $time   The touch time as a Unix timestamp, if not supplied the current system time is used
     * @param ?int<UnixTimestamp>          $atime  The access time as a Unix timestamp, if not supplied the current system time is used
     *
     * @return bool
     */
    public static function touch( string | iterable $files, ?int $time = null, ?int $atime = null ) : bool {
        try {
            ( new Filesystem() )->touch( $files, $time, $atime );
            return true;
        }
        catch ( IOException $e ) {
            Log::Error( message : $e->getMessage(), context : [ 'exception' => $e ] );
        }
        return false;
    }

    /**
     * Removes files or directories.
     */
    public static function remove( string | iterable $files ) : bool {
        try {
            ( new Filesystem() )->remove( $files );
            return true;
        }
        catch ( IOException $e ) {
            Log::Error( message : $e->getMessage(), context : [ 'exception' => $e ] );
        }
        return false;
    }

    /**
     * Creates a directory recursively.
     */
    public static function mkdir(
        string | iterable $dirs,
        int               $mode = 0777,
        bool              $returnPath = true,
    ) : bool | string | array {
        try {
            ( new Filesystem() )->mkdir( $dirs, $mode );
            return $returnPath ? $dirs : true;
        }
        catch ( IOException $e ) {
            Log::Error( message : $e->getMessage(), context : [ 'exception' => $e ] );
        }
        return false;
    }

    /**
     * Get the file size of a given file.
     *
     * @param string<PathString>|int<Bytes>  $bytes  Provide a path to a file or a file size in bytes
     *
     * @return string
     */
    public static function size( string | int $bytes ) : string {

        if ( is_string( $bytes ) ) {
            if ( !file_exists( $bytes ) ) {
                Log::Error( '{path} does not exist.', [ 'path' => $bytes, ] );
                return 'Unknown';
            }
            $bytes = filesize( $bytes );
        }
        $unitDecimalsByFactor = [
            [ 'B', 0 ],
            [ 'kB', 0 ],
            [ 'MB', 2 ],
            [ 'GB', 2 ],
            [ 'TB', 3 ],
            [ 'PB', 3 ],
        ];

        $factor = $bytes ? floor( log( (int) $bytes, 1024 ) ) : 0;
        $factor = min( $factor, count( $unitDecimalsByFactor ) - 1 );

        $value = round( $bytes / ( 1024 ** $factor ), $unitDecimalsByFactor[ $factor ][ 1 ] );
        $units = $unitDecimalsByFactor[ $factor ][ 0 ];

        return $value . $units;
    }

    /**
     * @deprecated Use Symfony\Finder instead.
     * @link       https://symfony.com/doc/current/components/finder.html  Symfony Finder Documentation
     */
    public static function scanDirectories(
        string | array $directories,
        bool           $includeDirectories = false,
        bool           $addUnexpectedValue = false,
        bool           $returnFinder = true,
    ) : array | Finder {
        throw new LogicException( 'Breaking change: Use Symfony\Finder instead.' );
    }
}