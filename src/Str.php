<?php

namespace Northrook\Support;

use JetBrains\PhpStorm\Pure;
use voku\helper\ASCII;
if ( ! function_exists( 'mb_strtolower' ) ) {
	/** Fallback for mb_strtolower */
	function mb_strtolower( ?string $string ) : string { return strtolower( $string ); }
}

if ( ! function_exists( 'mb_substr' ) ) {
	/** Fallback for mb_substr */
	function mb_substr( ?string $string, int $start = 0, int $length = null ) : string {
		return substr( $string, $start, $length );
	}
}


final class Str {
	
	/** Convert $value to ASCII
	 *
	 * @param string	$value
	 * @param string	$language
	 *
	 * @return string
	 */
	public static function ascii( $value, $language = 'en' ) : string {
		return ASCII::to_ascii( (string) $value, $language );
	}
	
	/** Extract acronym from a $string
	 *
	 * @param ?string	$string		The string to process
	 * @param bool		$capitalize	Defaults to true
	 * @param string	$separator	Defaults to single whitespace
	 *
	 * @return ?string Acronym, or null if $string is null
	 */
	public static function acronym( ?string $string, bool $capitalize = true, string $separator = ' ' ) : ?string {
		if ( ! $string ) return null;
		$acronyms	= array_map(
			static fn( string $name ) => mb_substr( $name, 0, 1 ),
			explode( $separator, $string )
		);
		$acronyms	= implode( '', $acronyms );
		return $capitalize
			? strtoupper( $acronyms )
			: $acronyms;
	}
	
	/// use Render::element(); instead, allow passing attributes
	public static function osKeybind( ?string $string, ?string $tag = 'kbd' ) : ?string {
		if ( ! $string ) return null;
		if ( UserAgent::OS( 'apple' ) ) $string = str_replace( [ 'ctrl', 'alt' ], [ '⌘', '⌥' ], $string );
		return "<$tag>$string</$tag>";
	}
	
	/** Remove all "extra" blank space from the given string.
	 *
	 * * Removes Twig, CSS, inline JavaScript, and HTML comments by default
	 * * Does not perform __any__ sanitization
	 *
	 * @param string	$string
	 * @param bool		$preserveComments
	 * @param bool		$spacesOnly Preserve newlines
	 *
	 * @return string minified string
	 */
	public static function squish( string $string, bool $preserveComments = false, bool $spacesOnly = false ) : string {
		if ( ! $preserveComments ) {
			$string = preg_replace(
				[
					'/<!--(.*?)-->/ms',
					'/{#(.*?)#}/ms',
					'/\/\*(.*?)\*\//ms',
					'/\/\/.*/m',
				],
				'',
				$string );
		}
		if ( $spacesOnly ) {
			return preg_replace(
				[
					'/^\s*?$\n/m',
					'/ +/',
				],
				' ',
				$string );
		}
		
		return preg_replace(
			'~(\s|\x{3164})+~u', ' ',
			preg_replace( '~^[\s\x{FEFF}]+|[\s\x{FEFF}]+$~u', '', $string ) );
	}
	
	public static function filepath( string $path, ?string $fullPath = null ) : string {
		$path = str_replace( [ '/', '\\' ], DIRECTORY_SEPARATOR, $path );
		return str_replace( '\\\\', '\\', $fullPath ? Str::start( string : $path, with : $fullPath ) : $path );
	}
	
	/**
	 * @param string|null	$string
	 * @param bool			$trim
	 * @param string		$separator // [camelCase, kebabCase, snakeCase][%any
	 *
	 * @param string|null	$language
	 *
	 * @return string|null
	 */
	public static function key( ?string $string, bool $trim = false, string $separator = 'camelCase', ?string $language = 'en' ) : ?string {
		
		if ( ! $string ) return null;
		$string	= mb_strtolower( $string );
		$string	= strip_tags( $string );
		
		$string = $language ? Str::ascii( $string, $language ) : $string;
		
		$string = str_replace( [ ' ', '-', '_', $separator ], ' ', $trim ? trim( $string ) : $string );
		
		if ( $separator === 'camelCase' ) return Str::toCamel( $string );
		
		
		return preg_replace( '/\W+/', $separator, $string );
	}
	
	// Different from key() in that it trims unnecessary words, such as "the"; specific for slug use
	// pass array of words to parse, e.g. [ 'the', 'of' ], pass key/value to replace, e.g. [ 'the', 'of', [ '@' => 'at' ], .. ]
	public static function slug( ?string $string, bool $trim = false, string $separator = '-' ) : ?string {
		return Str::key( $string, $trim, $separator );
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
	public static function contains( ?string $string, string | iterable $substrings, bool $caseSensitive = false ) : bool | string {
		
		if ( ! $caseSensitive ) $string = mb_strtolower( $string );
		
		if ( ! is_iterable( $substrings ) ) $substrings = (array) $substrings;
		
		foreach ( $substrings as $substring ) {
			if ( ! $caseSensitive ) $substring = mb_strtolower( $substring );
			
			if ( $substring !== '' && str_contains( $string, $substring ) ) {
				return $substring;
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
	
	/**
	 * Wrap the string with the given strings.
	 *
	 * @param ?string		$value
	 * @param string		$before
	 * @param string|null	$after
	 *
	 * @return string
	 */
	public static function wrap( ?string $value, string $before, ?string $after = null ) : string {
		// if ( str_starts_with($before, '<')) .. wrap $after in </$before>
		return $before . $value . ( $after ??= $before );
	}
	
	/**
	 * Parse a Class[@]method style callback into class and method.
	 *
	 * @param string		$callback
	 * @param string|null	$default
	 *
	 * @return array<int, string|null>
	 */
	public static function parseCallback( $callback, $default = null ) {
		return Str::contains( $callback, '@' ) ? explode( '@', $callback, 2 ) : [ $callback, $default ];
	}
	
	public static function case( ?string $string, string $case ) : ?string {
		return $string;
	}
	
	/** Convert a $string to camelCase.
	 *
	 * @param ?string $string
	 *
	 * @return ?string
	 */
	#[Pure] public static function toCamel( ?string $string ) : ?string {
		$delimiter	= Str::guessDelimiter( $string ) ?? ' ';
		$string		= mb_strtolower( $string );
		$camel		= [];
		$each		= explode( $delimiter, $string );
		
		if ( ! $each ) return $string;
		
		foreach ( $each as $index => $segment ) {
			if ( $index === 0 ) {
				$camel[] = $segment;
			}
			else $camel[] = ucfirst( $segment );
		}
		return implode( '', $camel );
	}
	
	#[Pure] public static function guessDelimiter( ?string $string ) : string {
		return Str::contains( $string, [ ' ', '-', '_', '/', '\\', ':', ';' ] );
	}
}