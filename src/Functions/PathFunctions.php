<?php

namespace Northrook\Support\Functions;

use Northrook\Support\Config;
use Northrook\Support\Str;

trait PathFunctions
{

	/** Check if a string is a valid URL
	 *
	 * @param  string  $string  The string to check
	 * @param  bool  $validate  Validate the URL
	 * @param  string  $scheme  The scheme to check against
	 *
	 * @link https://stackoverflow.com/a/68254092
	 *
	 */
	public static function isUrl( ?string $string, ?string $scheme = null, bool $validate = false ) : bool {
		// Bail if the $source is null, empty, or does not contain a scheme
		if ( !$string || false === str_contains( $string, '://' ) ) {
			return false;
		}

		$url = parse_url( $string );


		if ( false === $url || Config::security()->scheme !== $url[ 'scheme' ] ) {
			return false;
		}

		if ( $validate ) {

			$url = filter_var( $string, FILTER_VALIDATE_URL );

			if ( false === $url ) {
				return false;
			}

			$headers = get_headers( $string );

			if ( false === $headers ) {
				return false;
			}

			if ( false === str_contains( $headers[ 0 ], '200' ) ) {
				return false;
			}

			return false;
		}

		return true;


	}

	public static function filepath( string $path, ?string $fullPath = null ) : string {
		$path = str_replace( [ '/', '\\' ], DIRECTORY_SEPARATOR, $path );
		$path = mb_strtolower( $path );

		return str_replace(
			'\\\\', '\\', $fullPath ? Str::start(
			string : $path, with : Str::end(
			string : $fullPath,
			with   : DIRECTORY_SEPARATOR,
		),
		) : $path,
		);
	}

	public static function normalizePath( string $string ) : string {
		$path = [];
		foreach ( explode( '/', strtr( $string, '\\', '/' ) ) as $part ) {
			if ( $part === '..' && $path && end( $path ) !== '..' ) {
				array_pop( $path );
			}
			else {
				if ( $part !== '.' ) {
					$path[] = $part;
				}
			}
		}

		return implode( DIRECTORY_SEPARATOR, $path );
	}
}
