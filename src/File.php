<?php


namespace Northrook\Support;


final class File {
	
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