<?php

namespace Northrook\Support\Functions;

use Northrook\Support\Str;

trait PathFunctions {

	/**
	 *
	 * @lin https://stackoverflow.com/a/68254092
	 *
	 * @param  string|null   $string
	 * @param  bool          $trim
	 * @param  string        $separator  // [camelCase, kebabCase, snakeCase][%any]
	 * @param  string|null   $language
	 * @return string|null
	 */
	public static function isUrl( ?string $string, ?string $scheme = null ) {
		// Bail if the $source is null, empty, or does not contain a scheme
		if ( ! $string || strpos( $string, '://' ) === false ) {
			return false;
		}

		$url = parse_url( $string );

		return match ( $url['scheme'] ?? null ) {
			'http' => true, // @todo report warning to Debug, check if https version is valid, if not, return false
			'https' => true,
			default => false,
		};

	}

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
