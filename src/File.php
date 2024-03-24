<?php

namespace Northrook\Support;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use UnexpectedValueException;

abstract class File
{

    use ConfigParameters;

    public static function exists( string $path ) : bool {


        $path = Str::filepath(
            $path,
        //   static::config()->rootDir  // TODO: Overhauling config
        );

        return file_exists( $path );
    }

    /**
     * Get the file size of a given file.
     *
     *
     * @param string  $path
     *
     * @return int
     */
    public static function size( string $path ) : int {

        $path = Str::filepath( $path, static::config()->rootDir );

        return filesize( $path );
    }

    public static function getContents( string $path, string $onError = null, bool $asJson = false ) : ?string {
        $path = Str::filepath( path : $path );
        if ( !file_exists( $path ) ) {
            return null;
        }

        $content = file_get_contents( $path );

        if ( $content === false ) {
            return null;
        }

        if ( $asJson ) {
            $content = json_decode( $content, true );
        }

        return $content;
    }

    public static function putContents( ?string $content, string $filename, int $flags = 0, bool $override = true,
    ) : false | int {

        if ( is_null( $content ) ) {
            return false;
        }

        $filename = File::makeDirectory( $filename );

        if ( !$filename || ( !$override && file_exists( $filename ) ) ) {
            return false;
        }

        return file_put_contents( $filename, $content, $flags ) ?: false;
    }

    /**
     *
     * @param string  $path
     * @param int     $permissions
     *
     * @return string|null
     */
    public static function makeDirectory( string $path, int $permissions = 0755 ) : ?string {

        $path = Str::filepath( path : $path );
        $dir  = File::getDirectoryPath( $path );

        if ( File::isPathTraversable( path : $path ) ) {
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
            return File::makeDirectory( path : $path );
        }

        $path = Str::filepath( path : $path );

        return !file_exists( $path ) ? $onError : $path;
    }

    public static function getDirectoryPath( string $path ) : string {
        $path = substr( $path, 0, (int) strrpos( Str::filepath( $path ), DIRECTORY_SEPARATOR ) );

        return rtrim( $path, DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR;
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
                if ( str_starts_with( File::getFileName( $file->getPathname() ), '_' ) ) {
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