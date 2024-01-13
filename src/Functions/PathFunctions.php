<?php

namespace Northrook\Support\Functions;

use Northrook\Support\Str;

trait PathFunctions {

	public static function filepath( string $path, ?string $fullPath = null ): string {
		$path = str_replace( ['/', '\\'], DIRECTORY_SEPARATOR, $path );
		$path = mb_strtolower( $path );

		return str_replace( '\\\\', '\\', $fullPath ? Str::start( string : $path, with: Str::end( string: $fullPath, with: DIRECTORY_SEPARATOR ) ): $path );
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
