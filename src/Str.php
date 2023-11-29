<?php

namespace Northrook\Support;

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
	
	private static string	$_ASCII_LANGUAGE	= 'en';
	private static string	$_SLUG_SEPARATOR	= '-';
	
	public static function setSlugSeparator( string $separator ) : void {
		Str::$_SLUG_SEPARATOR = $separator;
	}
	
	public static function setAsciiLanguage( string $language ) : void {
		Str::$_ASCII_LANGUAGE = $language;
	}
	
	/** Convert $value to ASCII
	 *
	 * @param string	$value
	 * @param ?string	$language
	 *
	 * @return string
	 */
	public static function ascii( string $value, ?string $language = null ) : string {
		return ASCII::to_ascii( $value, $language ?? Str::$_ASCII_LANGUAGE );
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
	
	/** Remove all "extra" blank space from the given string.
	 *
	 * * Removes Twig, CSS, inline JavaScript, and HTML comments by default
	 * * Does not perform __any__ sanitization
	 *
	 * @param ?string	$string
	 * @param bool		$preserveComments
	 * @param bool		$spacesOnly Preserve newlines
	 *
	 * @return string minified string
	 */
	public static function squish( ?string $string, bool $preserveComments = false, bool $spacesOnly = false ) : string {
		if ( ! $string ) {
			return '';
		}
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
		$path	= str_replace( [ '/', '\\' ], DIRECTORY_SEPARATOR, $path );
		$path	= mb_strtolower( $path );
		return str_replace( '\\\\', '\\', $fullPath ? Str::start( string : $path, with : Str::end( string : $fullPath, with : DIRECTORY_SEPARATOR ) ) : $path );
	}
	
	/**
	 * @param string|null	$string
	 * @param bool			$trim
	 * @param string		$separator // [camelCase, kebabCase, snakeCase][%any]
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
	public static function slug( ?string $string, bool $trim = false, ?string $separator = null ) : ?string {
		return Str::key( $string, $trim, $separator ?? Str::$_SLUG_SEPARATOR );
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
	 * @param string			$callback // ['str_contains', 'str_starts_with', 'str_ends_with'][%any
	 *
	 * @return bool | string
	 */
	public static function contains( ?string $string, string | iterable $substrings, bool $caseSensitive = false, string $callback = 'str_contains' ) : bool | string {
		if ( ! in_array( $callback, [ 'str_contains', 'str_starts_with', 'str_ends_with' ] ) ) {
			$callback = 'str_contains';
		}
		if ( ! $caseSensitive ) $string = mb_strtolower( $string );
		
		if ( ! is_iterable( $substrings ) ) $substrings = (array) $substrings;
		
		foreach ( $substrings as $substring ) {
			if ( ! $caseSensitive ) $substring = mb_strtolower( $substring );
			
			if ( $substring !== '' && $callback( $string, $substring ) ) {
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
	
	/**  Wrap the string with the given strings.
	 *
	 * * If $before and $after are the same, $before will be used.
	 * * If $before is a `<tag>`, $after will be used as the closing `</tag>`.
	 * * * Supports tag attributes.
	 *
	 * @param ?string		$value
	 * @param string		$before
	 * @param string|null	$after
	 *
	 * @return string
	 */
	public static function wrap( ?string $value, string $before, ?string $after = null ) : string {
		if ( ! $after && str_starts_with( $before, '<' ) ) {
			$tag	= strstr( $before, ' ', true ) ?: $before;
			$after	= str_replace( '<', '</', rtrim( $tag, '>' ) . '>' );
		}
		else $after ??= $before;
		
		return $before . $value . $after;
	}
	
	/** Split a string by the given separator, with flexible return options.
	 *
	 * @param ?string	$string
	 * @param string	$return		= ['array', 'first', 'last'][any]
	 * @param string	$separator	= ':'
	 *
	 * @return array | string | null
	 */
	public static function split( ?string $string, string $return = 'array', string $separator = ':' ) : array | string | null {
		$array = array_filter( explode( $separator, $string ) );
		if ( ! $array ) return null;
		if ( $return === 'first' ) return array_shift( $array ) ?: null;
		if ( $return === 'last' ) return array_pop( $array ) ?: null;
		return $array;
	}
	
	/**
	 * Parse a Class[@]method style callback into class and method.
	 *
	 * @param string		$callback
	 * @param string|null	$default
	 *
	 * @return array<Num, string|null>
	 */
	public static function parseCallback( string $callback, ?string $default = null ) : array {
		return Str::contains( $callback, '@' ) ? explode( '@', $callback, 2 ) : [ $callback, $default ];
	}
	
	/** Convert a $string to camelCase.
	 *
	 * @param ?string $string
	 *
	 * @return ?string
	 */
    
     public static function toCamel( ?string $string ) : ?string {
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

    public static function guessDelimiter( ?string $string ) : string {
		return Str::contains( $string, [ ' ', '-', '_', '/', '\\', ':', ';' ] );
	}
}