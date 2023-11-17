<?php

namespace Northrook\Support;

final class Str {
	
	public static function filepath( string $path, ?string $fullPath = null ) : string {
		$path = str_replace( [ '/', '\\' ], DIRECTORY_SEPARATOR, $path );
		return str_replace( '\\\\', '\\', $fullPath ? Str::start( string : $fullPath, with : $path ) : $path );
	}
	
	public static function asKey( ?string $string, bool $trim = false, string $separator = '_' ) : ?string {
		if ( ! $string ) return null;
		$string	= strtolower( $string );
		$string	= strip_tags( $string );
		$string	= str_replace( ' ', $separator, $string );
		return $trim ? trim( $string ) : $string;
	}
	
	/** Returns a $string that starts $with a certain substring.
	 *
	 * * Passed null values will be stringified before comparison
	 *
	 * @param ?string	$string	The original string
	 * @param ?string	$with	The desired starting substring
	 * @param bool		$trim	Determines if the strings should be trimmed before parsing
	 *
	 * @return string The processed string
	 */
	public static function start( ?string $string, ?string $with, bool $trim = false ) : string {
		
		$string = $trim ? trim( $string ) : (string) $string;
		
		if ( $with === null || str_starts_with( $string, $with ) ) return $string;
		
		$with = $trim ? trim( $with ) : $with;
		
		return $with . $string;
	}
	
	/** Returns a $string that ends $with a certain substring.
	 *
	 * * Passed null values will be stringified before comparison
	 * * Avoid $trim when used in loops
	 *
	 * @param ?string	$string	The original string
	 * @param ?string	$with	The desired ending substring
	 * @param bool		$trim	Determines if the strings should be trimmed before parsing
	 *
	 * @return string The processed string
	 */
	public static function end( ?string $string, ?string $with, bool $trim = false ) : string {
		
		$string = $trim ? trim( $string ) : (string) $string;
		
		if ( $with === null || str_ends_with( $string, $with ) ) return $string;
		
		$with = $trim ? trim( $with ) : $with;
		
		return $string . $with;
	}
	
	/** Determine if a $string contains any provided $substrings.
	 *
	 * * Case Insensitive by default
	 *
	 * @param ?string			$string
	 * @param string|iterable	$substrings
	 * @param bool				$caseSensitive
	 *
	 * @return bool
	 */
	public static function contains( ?string $string, string | iterable $substrings, bool $caseSensitive = false ) : bool {
		
		if ( ! $caseSensitive ) $string = mb_strtolower( $string );
		
		if ( ! is_iterable( $substrings ) ) $substrings = (array) $substrings;
		
		foreach ( $substrings as $substring ) {
			if ( $caseSensitive ) $substring = mb_strtolower( $substring );
			
			if ( $substring !== '' && str_contains( $string, $substring ) ) {
				return true;
			}
		}
		
		return false;
	}
	
	/** Determine if a $string contains all $substrings.
	 *
	 *  * Case Insensitive by default
	 *
	 * @param ?string	$string
	 * @param iterable	$substrings
	 * @param bool		$caseSensitive
	 *
	 * @return bool
	 */
	public static function containsAll( ?string $string, iterable $substrings, bool $caseSensitive = false ) : bool {
		foreach ( $substrings as $substring ) {
			if ( ! Str::contains(
				$string,
				$substring,
				$caseSensitive
			) ) {
				return false;
			}
		}
		return true;
	}
	
	/** Replace each key from $array with its value, when found in $string.
	 *
	 * @param array		$array Must be key => value
	 * @param ?string	$string
	 * @param bool		$caseSensitive
	 *
	 * @return ?string The processed string, or null if $string is null
	 */
	public static function replaceEach( array $array, ?string $string, bool $caseSensitive = true ) : ?string {
		if ( ! $string ) return $string;
		$keys = array_keys( $array );
		
		return $caseSensitive
			? str_replace( $keys, $array, $string )
			: str_ireplace( $keys, $array, $string );
	}
	
}