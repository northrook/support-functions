<?php


namespace Northrook\Support;

abstract class File {
	
	public static function get( string $path, string $onError = null ) : ?string {
		$path = Str::filepath( path : $path );
		if ( ! file_exists( $path ) ) return $onError;
		return file_get_contents( $path ) ?: $onError;
	}
	
	public static function put( ?string $content, string $filename, int $flags = 0, bool $override = true ) : false | int {
		
		if ( is_null( $content ) ) return false;
		
		$filename = File::makeDirectory( $filename );
		
		if ( ! $filename ) return false;
		if ( ! $override && file_exists( $filename ) ) return false;
		
		$status = file_put_contents( $filename, $content, $flags );
		
		if ( ! $status ) return false;
		
		return $status;
		
		// $path = Str::before( $path, '/' );
		// Debug::print( $filename );
		// $context = stream_context_create( $context );
		// Log::notice( $context );
		// header( 'Content-Type: text/css; charset=UTF-8' );
		// $context = stream_context_create(
		// 	[
		// 		'http' => [
		// 			'method'  => 'POST',
		// 			'header'  => "Content-Type: application/json\r\n" .
		// 			             "Accept: application/json\r\n" .
		// 			             "X-Content-Type-Options: nosniff",
		// 			'content' => $content,
		// 			'timeout' => 60,
		// 		],
		// 	] );
		// $content = utf8_encode( $content );
		// var_dump( mb_detect_encoding( $content ), basename( $filename ) );
		// header( 'X-Content-Type-Options: nosniff' );
		
		// var_dump( get_headers( Get::asUrl( $filename ), true ) );
		
		// if ( $status ) Log::info( 'Wrote file to disk: ' . basename( $filename ) );
		// if ( ! $status ) Log::warning( 'Unable to write to disk: ' . basename( $filename ) );
		
		// return false;
	}
	
	public static function path( string $path, bool $create = false, ?string $onError = null ) : ?string {
		if ( $create ) return File::makeDirectory( path : $path );
		$path = Str::filepath( path : $path );
		if ( ! file_exists( $path ) ) return $onError;
		return $path;
	}
	
	/**
	 * @param string	$path
	 * @param int		$permissions
	 *
	 * @return string|null
	 */
	public static function makeDirectory( string $path, int $permissions = 0755 ) : ?string {
		
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