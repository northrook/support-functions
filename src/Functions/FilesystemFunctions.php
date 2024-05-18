<?php

namespace Northrook\Support\Functions;

use JetBrains\PhpStorm\Deprecated;
use JetBrains\PhpStorm\ExpectedValues;
use Northrook\Core\Type\PathType;
use Northrook\Logger\Log;
use Northrook\Support\Str;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use UnexpectedValueException;

trait FilesystemFunctions
{
    /**
     * Mimetypes for simple .extension lookup.
     *
     * @link https://developer.mozilla.org/en-US/docs/Web/HTTP/Basics_of_HTTP/MIME_types/Common_types
     */
    private const MIME_TYPES = [

        // Text and XML
        'txt'    => 'text/plain',
        'htm'    => 'text/html',
        'html'   => 'text/html',
        'php'    => 'text/html',
        'css'    => 'text/css',
        'js'     => 'application/javascript',

        // Documents
        'rtf'    => 'application/rtf',
        'doc'    => 'application/msword',
        'pdf'    => 'application/pdf',
        'eps'    => 'application/postscript',

        // Data sources
        'csv'    => 'text/csv',
        'json'   => 'application/json',
        'jsonld' => 'application/ld+json',
        'xls'    => 'application/vnd.ms-excel',
        'xml'    => 'application/xml',

        // Images and vector graphics
        'apng'   => 'image/png',
        'png'    => 'image/png',
        'jpe'    => 'image/jpeg',
        'jpeg'   => 'image/jpeg',
        'jpg'    => 'image/jpeg',
        'gif'    => 'image/gif',
        'bmp'    => 'image/bmp',
        'ico'    => 'image/vnd.microsoft.icon',
        'tiff'   => 'image/tiff',
        'tif'    => 'image/tiff',
        'svg'    => 'image/svg+xml',
        'svgz'   => 'image/svg+xml',
        'webp'   => 'image/webp',
        'webm'   => 'video/webm',

        // archives
        '7z'     => 'application/x-7z-compressed',
        'zip'    => 'application/zip',
        'rar'    => 'application/x-rar-compressed',
        'exe'    => 'application/x-msdownload',
        'msi'    => 'application/x-msdownload',
        'cab'    => 'application/vnd.ms-cab-compressed',
        'tar'    => 'application/x-tar',

        // audio/video
        'mp3'    => 'audio/mpeg',
        'qt'     => 'video/quicktime',
        'mov'    => 'video/quicktime',

        // Fonts
        'ttf'    => 'font/ttf',
        'otf'    => 'font/otf',
        'woff'   => 'font/woff',
        'woff2'  => 'font/woff2',
        'eot'    => 'application/vnd.ms-fontobject',

    ];

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
     * Checks the existence of files or directories.
     */
    public static function exists( string | iterable $files ) : bool {
        return ( new Filesystem() )->exists( $files );
    }


    public static function getMimeType( PathType | string $path ) : ?string {

        $type = $path instanceof PathType ? $path->extension : pathinfo( $path, PATHINFO_EXTENSION );

        return static::MIME_TYPES[ $type ] ?? null;
    }

    /**
     * Get the contents of a file.
     *
     * @param PathType | string  $path   Path to the file
     * @param bool               $cache  Cache the file contents for this request
     *
     * @return null|string  File contents, or null if the file does not exist
     */
    public static function getContents( PathType | string $path, bool $cache = true ) : ?string {

        if ( is_string( $path ) ) {
            $path = new PathType( $path );
        }

        if ( $cache && isset( static::$cache[ $path->value ] ) ) {
            return static::$cache[ $path->value ];
        }

        if ( !$path->exists ) {
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

        $content = ( new Filesystem() )->readFile( filename : $path );

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

    // todo : Implement a force-override option into static::save()
    #[Deprecated( 'Use ' . __CLASS__ . '::save() instead. Better support for larger files, and streamed resources.' )]
    public static function putContents( ?string $content, string $filename, int $flags = 0, bool $override = true,
    ) : false | int {
        return static::save( $filename, $content );
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
            return self::mkdir( $path );
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
            return PathType::normalize( $path );
        }

        $level = substr_count( $path, '../', 0, strrpos( $path, '../' ) + 3 );
        $root  = dirname( debug_backtrace()[ 0 ][ 'file' ], $level ?: 1 );
        $path  = $root . '/' . substr( $path, strrpos( $path, '../' ) + 3 );

        $path = PathType::normalize( $path );

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
                    RecursiveIteratorIterator::CHILD_FIRST,
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