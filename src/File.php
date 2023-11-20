<?php


namespace Northrook\Support;

abstract class File {
	
	
	public static function get( string $path, string $onError = null ) : ?string {
		$path = Str::filepath( path : $path );
		if ( ! file_exists( $path ) ) return $onError;
		return file_get_contents( $path ) ?: $onError;
	}
	
	public static function path( string $path, ?string $onError = null ) : string {
		return File::mkdir( path : $path );
	}
	
	/**
	 * @param string	$path
	 * @param int		$permissions
	 *
	 * @return string|null
	 */
	public static function mkdir( string $path, int $permissions = 0755 ) : ?string {
		
		$path	= Str::filepath( path : $path );
		$dir	= File::directoryPath( $path );
		
		if ( File::pathTraversable( path : $path ) ) return null;
		if ( file_exists( $path ) || is_dir( $dir ) ) return $path;
		
		if (
			mkdir( $dir, $permissions, true ) ||
			is_dir( $dir )
		) {
			return $path;
		}
		
		return null;
	}
	
	public static function pathTraversable( string $path ) : bool {
		return Str::contains(
			string		: $path,
			substrings	: [ ".", "../", ".." . DIRECTORY_SEPARATOR ],
			callback	: 'str_starts_with'
		);
	}
	
	public static function directoryPath( string $path ) : string {
		$path = substr( $path, 0, (int) strrpos( $path, DIRECTORY_SEPARATOR ) );
		return rtrim( $path, DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR;
	}
}