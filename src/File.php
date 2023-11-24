<?php


namespace Northrook\Support;

abstract class File {
	
	public static function getContents( string $path, string $onError = null ) : ?string {
		$path = Str::filepath( path : $path );
		if ( ! file_exists( $path ) ) {
			return $onError;
		}
		return file_get_contents( $path ) ?: $onError;
	}
	
	public static function putContents( ?string $content, string $filename, int $flags = 0, bool $override = true ) : false | int {
		
		if ( is_null( $content ) ) {
			return false;
		}
		
		$filename = File::makeDirectory( $filename );
		
		if ( ! $filename || ( ! $override && file_exists( $filename ) ) ) {
			return false;
		}
		
		return file_put_contents( $filename, $content, $flags ) ?: false;
	}
	
	/**
	 * @param string	$path
	 * @param int		$permissions
	 *
	 * @return string|null
	 */
	public static function makeDirectory( string $path, int $permissions = 0755 ) : ?string {
		
		$path	= Str::filepath( path : $path );
		$dir	= File::getDirectoryPath( $path );
		
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
		return Str::contains(
			string		: $path,
			substrings	: [ ".", "../", ".." . DIRECTORY_SEPARATOR ],
			callback	: 'str_starts_with'
		);
	}
	
	public static function getPath( string $path, bool $create = false, ?string $onError = null ) : ?string {
		if ( $create ) {
			return File::makeDirectory( path : $path );
		}
		
		$path = Str::filepath( path : $path );
		
		return ! file_exists( $path ) ? $onError : $path;
	}
	
	public static function getDirectoryPath( string $path ) : string {
		$path = substr( $path, 0, (int) strrpos( Str::filepath( $path ), DIRECTORY_SEPARATOR ) );
		return rtrim( $path, DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR;
	}
	
	/** Return the file name from a path.
	 *
	 * * Omits the extension by default.
	 *
	 * @param string		$path			The path
	 * @param bool<false>	$withExtension	Include the file extension
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
}