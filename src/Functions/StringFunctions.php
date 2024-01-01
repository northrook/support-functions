<?php

namespace Northrook\Support\Functions;

if ( ! function_exists( 'mb_strtolower' ) ) {
	/**
	 * Fallback for mb_strtolower
	 */
	function mb_strtolower( ?string $string ): string {return strtolower( $string );}
}

if ( ! function_exists( 'mb_substr' ) ) {
	/**
	 * Fallback for mb_substr
	 */
	function mb_substr( ?string $string, int $start = 0, int $length = null ): string {
		return substr( $string, $start, $length );
	}
}

trait StringFunctions {

	public static function filepath( string $path, ?string $fullPath = null ): string {
		$path = str_replace( ['/', '\\'], DIRECTORY_SEPARATOR, $path );
		$path = mb_strtolower( $path );

		return str_replace( '\\\\', '\\', $fullPath ? static::start( string : $path, with: static::end( string: $fullPath, with: DIRECTORY_SEPARATOR ) ): $path );
	}

	public static function normalizePath( string $path ): string {
		$res = [];
		foreach ( explode( '/', strtr( $path, '\\', '/' ) ) as $part ) {
			if ( $part === '..' && $res && end( $res ) !== '..' ) {
				array_pop( $res );
			} elseif ( $part !== '.' ) {
				$res[] = $part;
			}
		}

		return implode( DIRECTORY_SEPARATOR, $res );
	}
}
