<?php

namespace Northrook\Support;

use LogicException;
use Northrook\Core\Interface\Printable;
use Northrook\Core\Trait\PrintableClass;
use Northrook\Core\Trait\PropertyAccessor;
use Northrook\Logger\Log;
use Northrook\Support\Internal\MimeTypeTrait;
use Northrook\Support\String\PathFunctions;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use function Northrook\Core\Function\normalizePath;

/**
 * @template PathString as string
 * @template UnixTimestamp as int
 * @template Bytes as int
 *
 * @property-read  string $path
 * @property-read  string $basename
 * @property-read  string $filename
 * @property-read  string $extension
 * @property-read  bool   $exists
 * @property-read  bool   $isDir
 * @property-read  bool   $isFile
 * @property-read  bool   $isUrl
 * @property-read  bool   $isWritable
 * @property-read  int    $lastModified
 * @property-read  string $mimeType
 */
final class File implements Printable
{
    use MimeTypeTrait, PathFunctions, PrintableClass, PropertyAccessor;

    private static string $rootPath;
    private static string $publicPath;

    private string $path;
    private string $mimeType;


    public function __construct(
        string $path,
    ) {
        $this->path = normalizePath( $path );
    }

    public function __get( string $property ) {
        return match ( $property ) {
            'path'         => $this->path,
            'exists'       => File::exists( $this->path ),
            'basename'     => pathinfo( $this->path, PATHINFO_BASENAME ),
            'filename'     => pathinfo( $this->path, PATHINFO_FILENAME ),
            'extension'    => pathinfo( $this->path, PATHINFO_EXTENSION ),
            'isDir'        => is_dir( $this->path ),
            'isFile'       => is_file( $this->path ),
            'isUrl'        => Str::isUrl( $this->path ),
            'isWritable'   => is_writable( $this->path ),
            'isReadable'   => is_readable( $this->path ),
            'lastModified' => filemtime( $this->path ),
            'mimeType'     => $this->mimeType ??= File::getMimeType( $this->path ),
            default        => null,
        };
    }

    public function readContent() : ?string {
        return File::read( $this->path );
    }

    public function saveContent( string $content ) : bool {
        return File::save( $this->path, $content );
    }

    public function copyTo( string $path, bool $overwriteNewerFiles = false ) : bool {
        return File::copy( $this->path, $path, $overwriteNewerFiles );
    }

    public function __toString() : string {
        return $this->path;
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
     * - {@see IOException} will be caught and logged as an error, returning `null`
     *
     * @param string<PathString>  $path  The path to the file
     *
     * @return ?string Returns the contents of the file, or null if an {@see IOException} was thrown
     *
     */
    public static function read( string $path ) : ?string {
        try {
            return ( new Filesystem() )->readFile( $path );
        }
        catch ( IOException $exception ) {
            Log::exception( $exception );
        }
        return null;
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
        catch ( IOException $exception ) {
            Log::exception( $exception );
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
        catch ( FileNotFoundException | IOException $exception ) {
            Log::exception( $exception );
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
        catch ( IOException $exception ) {
            Log::exception( $exception );
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
        catch ( IOException $exception ) {
            Log::exception( $exception );
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
        catch ( IOException $exception ) {
            Log::exception( $exception );
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
        catch ( IOException $exception ) {
            Log::exception( $exception );
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
    ) : array {
        throw new LogicException( 'Breaking change: Use Symfony\Finder instead.' );
    }

    private function validate() : bool {
        return $this->exists = File::exists( $this->path );
    }
}