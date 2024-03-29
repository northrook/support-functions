<?php

namespace Northrook\Support\Functions;

use JetBrains\PhpStorm\Deprecated;
use JetBrains\PhpStorm\ExpectedValues;
use Northrook\Logger\Log;
use Northrook\Support\Str;
use Northrook\Types\Path;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use UnexpectedValueException;

trait FilesystemFunctions
{
    private static array $cache = [];

    /**
     * Sets access and modification time of file.
     *
     * @param int|null  $time   The touch time as a Unix timestamp, if not supplied the current system time is used
     * @param int|null  $atime  The access time as a Unix timestamp, if not supplied the current system time is used
     *
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
     * Get the file size of a given file.
     *
     *
     * @param string|int  $bytes  Provide a path to a file or a file size in bytes
     *
     * @return string
     */
    public static function size( string | int $bytes ) : string {

        if ( is_string( $bytes ) && file_exists( $bytes ) ) {
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

        $value = round( $bytes / pow( 1024, $factor ), $unitDecimalsByFactor[ $factor ][ 1 ] );
        $units = $unitDecimalsByFactor[ $factor ][ 0 ];

        return $value . $units;
    }

    /**
     * Checks the existence of files or directories.
     */
    public static function exists( string | iterable $files ) : bool {
        return ( new Filesystem() )->exists( $files );
    }


    public static function getMimeType( Path | string $path ) : ?string {
        $types = static::$cache[ 'mime.types' ] ??= include( self::parameterDirname(
            '../../resources/mimetypes.php',
        ) );

        if ( array_key_exists( $path->extension, $types ) ) {
            return $types[ $path->extension ];
        }

        return null;
    }

    public static function getContents( Path | string $path, bool $cache = true ) : ?string {

        if ( is_string( $path ) ) {
            $path = new Path( $path );
        }

        if ( $cache && isset( static::$cache[ $path->value ] ) ) {
            return static::$cache[ $path->value ];
        }

        if ( !$path->isValid ) {
            Log::Error(
                'The file {key} was parsed, but {error}. No file was found.',
                [
                    'key'   => $path->value,
                    'error' => 'does not exist',
                    'path'  => $path,
                ],
            );
            return null;
        }

        $content = file_get_contents( $path );

        if ( $path->extension === 'svg' ) {
            $content = str_replace(
                [ ' xmlns="http://www.w3.org/2000/svg"', ' xmlns:xlink="http://www.w3.org/1999/xlink"' ],
                '',
                $content,
            );
        }

        if ( $cache ) {
            static::$cache[ $path->value ] = $content;
        }

        return $content;
    }

    /**
     * Atomically dumps content into a file.
     *
     * @param string|resource  $content  The data to write into the file
     *
     * @throws IOException if the file cannot be written to
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

    #[Deprecated( 'Use ' . __CLASS__ . '::save() instead. Better support for larger files, and streamed resources.' )]
    public static function putContents( ?string $content, string $filename, int $flags = 0, bool $override = true,
    ) : false | int {

        if ( is_null( $content ) ) {
            return false;
        }

        $filename = self::makeDirectory( $filename );

        if ( !$filename || ( !$override && file_exists( $filename ) ) ) {
            return false;
        }

        return file_put_contents( $filename, $content, $flags ) ?: false;
    }


    /**
     * Creates a directory recursively.
     */
    public static function mkdir( string | iterable $dirs, int $mode = 0777 ) : bool {
        try {
            ( new Filesystem() )->mkdir( $dirs, $mode );
            return true;
        }
        catch ( IOException $e ) {
            Log::Error( message : $e->getMessage(), context : [ 'exception' => $e ] );
        }
        return false;
    }


    /**
     *
     * @param string  $path
     * @param int     $permissions
     *
     * @return string|null
     */
    #[Deprecated( 'Use ' . __CLASS__ . '::mkdir() instead.' )]
    public static function makeDirectory( string $path, int $permissions = 0755 ) : ?string {

        $path = Str::filepath( path : $path );
        $dir  = self::getDirectoryPath( $path );

        if ( self::isPathTraversable( path : $path ) ) {
            return null;
        }

        if ( file_exists( $path ) || is_dir( $dir ) ) {
            return $path;
        }

        if (
            mkdir( $dir, $permissions, true ) ||
            is_dir( $dir )
        ) {
            return $path;
        }

        return null;
    }

    public static function isPathTraversable( string $path ) : bool {
        return Str::contains( $path, [ ".", "../", ".." . DIRECTORY_SEPARATOR ] );
    }

    public static function getPath( string $path, bool $create = false, ?string $onError = null ) : ?string {
        if ( $create ) {
            return self::makeDirectory( path : $path );
        }

        $path = Str::filepath( path : $path );

        return !file_exists( $path ) ? $onError : $path;
    }

    public static function getDirectoryPath( string $path ) : string {
        $path = substr( $path, 0, (int) strrpos( Str::filepath( $path ), DIRECTORY_SEPARATOR ) );

        return rtrim( $path, DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR;
    }

    public static function parameterDirname(
        string  $path = '%kernel.project_dir%',
        #[ExpectedValues( [ 'log', 'error', 'exception' ] )]
        ?string $onInvalidPath = 'exception',
    ) : ?string {

        if ( false === str_starts_with( $path, '../' ) ) {
            return Path::normalize( $path );
        }

        $level = substr_count( $path, '../', 0, strripos( $path, '../' ) + 3 );
        $root  = dirname( debug_backtrace()[ 0 ][ 'file' ], $level ?: 1 );
        $path  = $root . '/' . substr( $path, strripos( $path, '../' ) + 3 );

        $path = Path::normalize( $path );

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
     * Return the file name from a path.
     *
     *  Omits the extension by default.
     *
     *
     * @param string       $path           The path
     * @param bool<false>  $withExtension  Include the file extension
     *
     * @return string
     */
    public static function getFileName( string $path, bool $withExtension = false ) : string {

        $name = basename( path : Str::filepath( $path ) );

        if ( $withExtension ) {
            return $name;
        }

        $hasExtension = strrpos( $name, '.' );

        if ( $hasExtension === false ) {
            return $name;
        }

        return substr( $name, 0, $hasExtension );
    }

    public static function scanDirectories(
        string | array $path, bool $includeDirectories = false, bool $addUnexpectedValue = false,
    ) : array {
        $files       = [];
        $underscored = [];
        foreach ( (array) $path as $scan ) {
            try {
                $iterator = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator( $scan ),
                    RecursiveIteratorIterator::CHILD_FIRST
                );
            }
            catch ( UnexpectedValueException ) {
                if ( $addUnexpectedValue ) {
                    $files[] = $scan;
                }
                continue;
            }
            foreach ( $iterator as $file ) {
                if ( !$includeDirectories && $file->isDir() ) {
                    continue;
                }
                if ( str_starts_with( self::getFileName( $file->getPathname() ), '_' ) ) {
                    // var_dump( File::getDirectoryPath( $file->getPathname() ) );
                    // array_unshift( $underscored, $file->getPathname() );
                    $underscored[] = $file->getPathname();
                    continue;
                }
                $files[] = $file->getPathname();
            }
        }

        usort( $underscored, static fn ( $a, $b ) => strlen( $a ) <=> strlen( $b ) );

        return array_merge( $underscored, $files );
    }
}