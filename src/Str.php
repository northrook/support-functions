<?php

namespace Northrook\Support;

use Northrook\Support\Functions\StringFunctions;
use voku\helper\ASCII;

final class Str {

	use StringFunctions;

	private static string $_ASCII_LANGUAGE = 'en';
	private static string $_SLUG_SEPARATOR = '-';

	public static function setSlugSeparator( string $separator ): void {
		Str::$_SLUG_SEPARATOR = $separator;
	}

	public static function setAsciiLanguage( string $language ): void {
		Str::$_ASCII_LANGUAGE = $language;
	}

	/**
	 * Convert $value to ASCII
	 *
	 *
	 * @param  string   $value
	 * @param  ?string  $language
	 * @return string
	 */
	public static function ascii( string $value, ?string $language = null ): string {
		return ASCII::to_ascii( $value, $language ?? Str::$_ASCII_LANGUAGE );
	}

	/**
	 * Extract acronym from a $string
	 *
	 *
	 * @param  ?string $string     The string to process
	 * @param  bool    $capitalize Defaults to true
	 * @param  string  $separator  Defaults to single whitespace
	 * @return ?string Acronym, or null if $string is null
	 */
	public static function acronym( ?string $string, bool $capitalize = true, string $separator = ' ' ): ?string {
		if ( ! $string ) {
			return null;
		}

		$acronyms = array_map(
			static fn( string $name ) => mb_substr( $name, 0, 1 ),
			explode( $separator, $string )
		);
		$acronyms = implode( '', $acronyms );

		return $capitalize
			? strtoupper( $acronyms )
			: $acronyms;
	}

	/**
	 * Remove all "extra" blank space from the given string.
	 *
	 *  Removes Twig, CSS, inline JavaScript, and HTML comments by default
	 *  Does not perform __any__ sanitization
	 *
	 *
	 * @param  ?string $string
	 * @param  bool    $preserveComments
	 * @param  bool    $spacesOnly         Preserve newlines
	 * @return string  minified string
	 */
	public static function squish( ?string $string, bool $preserveComments = false, bool $spacesOnly = false ): string {
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
			$string = preg_replace(
				[
					'/^\s*?$\n/m',
					'/ +/',
				],
				' ',
				$string
			);
		} else {
			$string = preg_replace(
				'~(\s|\x{3164})+~u',
				' ',
				preg_replace(
					'~^[\s\x{FEFF}]+|[\s\x{FEFF}]+$~u',
					'',
					$string
				)
			);
		}

		return str_replace(
			[' >', ' />', '> <', '> ', ' <'],
			['>', '/>', '><', '>', '<'],
			$string
		);
	}

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

	/** Check if a string contains only numbers
	 *
	 * * Returns `false` if the string contains non-numeric characters
	 * * Returns `$string` cast to int if the string contains only numbers
	 *
	 * @param null|string $string
	 * @return int|bool
	 */
	public static function isNumeric( ?string $string ): int | bool {

		return ( preg_match( '/^\d+$/', $string ) ) ? (int) $string : false;
	}

	public static function url( ?string $string, bool $absolute = false, bool $trailing = false ): ?string {

		$url = filter_var( $string, FILTER_SANITIZE_URL );

		$url = trim( $url, '/' );

		if ( $trailing ) {
			$url = rtrim( $url, '/' );
		}

		if ( ! $absolute ) {
			$url = '/' . $url;
		} else {
			$url = $_SERVER['SERVER_NAME'] . '/' . $url;
		}

		return $url;
	}

	public static function key(
			?string $string,
			?string $separator = 'camelCase',
			?string $preserve = null,
			?string $language = 'en'
	): ?string {

		if ( ! $string ) {
			return null;
		}

		$string = mb_strtolower( $string );
		$string = strip_tags( $string );

		$string = $language ? Str::ascii( $string, $language ) : $string;

		if ( $separator !== null ) {
			$string = str_replace( [' ', '-', '_', $separator], ' ', trim( $string ) );
		}

		if ( $separator === 'camelCase' ) {
			return Str::toCamel( $string );
		}

		$key = preg_replace( "/[^\w$preserve]+/", $separator, $string );

		return $key;
	}

	// Different from key() in that it trims unnecessary words, such as "the"; specific for slug use
	// pass array of words to parse, e.g. [ 'the', 'of' ], pass key/value to replace, e.g. [ 'the', 'of', [ '@' => 'at' ], .. ]
	public static function slug( ?string $string, ?string $separator = null ): ?string {
		return Str::key( $string, $separator ?? Str::$_SLUG_SEPARATOR );
	}

	/**
	 * Returns a $string that starts $with a certain substring.
	 *
	 *  Passed null values will be stringified before comparison
	 *
	 *
	 * @param  ?string $string The original string
	 * @param  ?string $with   The desired starting substring
	 * @param  bool    $trim   Determines if the strings should be trimmed before parsing
	 * @return string  The processed string
	 */
	public static function start( ?string $string, ?string $with, bool $trim = false ): string {

		$string = $trim ? trim( $string ) : (string) $string;

		if ( $with === null || str_starts_with( $string, $with ) ) {
			return $string;
		}

		$with = $trim ? trim( $with ) : $with;

		return $with . $string;
	}

	/**
	 * Returns a $string that ends $with a certain substring.
	 *
	 *  Passed null values will be stringified before comparison
	 *  Avoid $trim when used in loops
	 *
	 *
	 * @param  ?string $string The original string
	 * @param  ?string $with   The desired ending substring
	 * @param  bool    $trim   Determines if the strings should be trimmed before parsing
	 * @return string  The processed string
	 */
	public static function end( ?string $string, ?string $with, bool $trim = false ): string {

		$string = $trim ? trim( $string ) : (string) $string;

		if ( $with === null || str_ends_with( $string, $with ) ) {
			return $string;
		}

		$with = $trim ? trim( $with ) : $with;

		return $string . $with;
	}

	/**
	 * Determine if a $string contains any provided $substrings.
	 *
	 *  Case Insensitive by default
	 *
	 *
	 * @param  ?string         $string
	 * @param  string|iterable $substrings
	 * @param  bool            $caseSensitive
	 * @param  string          $callback        // ['str_contains', 'str_starts_with', 'str_ends_with'][%any
	 * @return bool            | string
	 */
	public static function contains( ?string $string, string | iterable $substrings, bool $caseSensitive = false, string $callback = 'str_contains' ): bool | string {
		if ( ! in_array( $callback, ['str_contains', 'str_starts_with', 'str_ends_with'] ) ) {
			$callback = 'str_contains';
		}
		if ( ! $caseSensitive ) {
			$string = mb_strtolower( $string );
		}

		if ( ! is_iterable( $substrings ) ) {
			$substrings = (array) $substrings;
		}

		foreach ( $substrings as $substring ) {
			if ( ! $caseSensitive ) {
				$substring = mb_strtolower( $substring );
			}

			if ( $substring !== '' && $callback( $string, $substring ) ) {
				return $substring;
			}
		}

		return false;
	}

	/**
	 * Determine if a $string contains all $substrings.
	 *
	 *  * Case Insensitive by default
	 *
	 *
	 * @param  ?string  $string
	 * @param  iterable $substrings
	 * @param  bool     $caseSensitive
	 * @return bool
	 */
	public static function containsAll( ?string $string, iterable $substrings, bool $caseSensitive = false ): bool {
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

	/** Determine if a $string starts with any $substrings.
	 *
	 *  * Case Insensitive by default
	 *
	 *
	 * @param  ?string  $string
	 * @param  string|array $substrings
	 * @param  bool     $caseSensitive
	 * @return bool
	 */
	public static function startsWith( ?string $string, string | array $substrings, bool $caseSensitive = false ): bool {
		foreach ( (array) $substrings as $substring ) {
			if ( str_starts_with(
				$string,
				$caseSensitive ? $substring : mb_strtolower( $substring ),
			) ) {
				return true;
			}
		}

		return false;
	}

	public static function after( string $string, string $needle, bool $last = false ): ?string {

		if ( $last ) {
			$needle = strrpos( $string, $needle );
		} else {
			$needle = strpos( $string, $needle );
		}

		if ( $needle !== false ) {
			return substr( $string, $needle + 1 );
		}

		return $string;

	}

	public static function before( string $string, string $needle, bool $last = false ): ?string {

		if ( $last ) {
			$needle = strrpos( $string, $needle );
		} else {
			$needle = strpos( $string, $needle );
		}

		if ( $needle !== false ) {
			return substr( $string, 0, $needle );
		}

		return $string;
	}

	/** Replace each key from `$map` with its value, when found in `$content`.
	 *
	 * @param  array			$map search:replace
	 * @param  string|array $content
	 * @param  bool    		$caseSensitive
	 * @return ?string The processed `$content`, or null if `$content` is empty
	 */
	public static function replaceEach(
		array $map,
		string | array $content,
		bool $caseSensitive = true
	): string | array | null {

		if ( ! $content ) {
			return $content;
		}

		$keys = array_keys( $map );

		return $caseSensitive
			? str_replace( $keys, $map, $content )
			: str_ireplace( $keys, $map, $content );
	}

	/**
	 * @param  iterable            $iterable
	 * @param  callable<value:key> $callback   Must accept $key and $value, and return ?string
	 * @return null|string
	 */
	public static function forEach( iterable $iterable, callable $callback, string | bool $implode = false ): mixed {

		$return = [];

		foreach ( $iterable as $key => $value ) {
			$return[] = $callback( $value, $key );
		}

		if ( $implode ) {
			return implode( $implode === true ? '' : $implode, $return ) ?? null;
		}

		return $return;
	}

	/**
	 * Wrap the string with the given strings.
	 *
	 *  If $before and $after are the same, $before will be used.
	 *  If $before is a `<tag>`, $after will be used as the closing `</tag>`.
	 *  * Supports tag attributes.
	 *
	 *
	 * @param  ?string     $value
	 * @param  string      $before
	 * @param  string|null $after
	 * @return string
	 */
	public static function wrap( ?string $value, string $before, ?string $after = null ): string {
		if ( ! $after && str_starts_with( $before, '<' ) ) {
			$tag   = strstr( $before, ' ', true ) ?: $before;
			$after = str_replace( '<', '</', rtrim( $tag, '>' ) . '>' );
		} else {
			$after ??= $before;
		}

		return $before . $value . $after;
	}

	/**
	 * Split a string by the given separator, with flexible return options.
	 *
	 *
	 * @param  ?string $string
	 * @param  string  $return    = ['array', 'first', 'last'][any]
	 * @param  string  $separator = ':'
	 * @return array   | string | null
	 */
	public static function split( ?string $string, string $return = 'array', string $separator = ':' ): array | string | null {
		$array = array_filter( explode( $separator, $string ) );
		if ( ! $array ) {
			return null;
		}

		if ( $return === 'first' ) {
			return array_shift( $array ) ?: null;
		}

		if ( $return === 'last' ) {
			return array_pop( $array ) ?: null;
		}

		return $array;
	}

	/**
	 * Parse a Class[@]method style callback into class and method.
	 *
	 *
	 * @param  string      $callback
	 * @param  string|null $default
	 * @return array<Num,  string|null>
	 */
	public static function ParseCallback( string $callback, ?string $default = null ): array {
		return Str::contains( $callback, '@' ) ? explode( '@', $callback, 2 ) : [$callback, $default];
	}

	/**
	 * Convert a $string to camelCase.
	 *
	 *
	 * @param  ?string   $string
	 * @return ?string
	 */

	public static function toCamel( ?string $string ): ?string {
		$delimiter = Str::guessDelimiter( $string ) ?? ' ';
		$string    = mb_strtolower( $string );
		$camel     = [];
		$each      = explode( $delimiter, $string );

		if ( ! $each ) {
			return $string;
		}

		foreach ( $each as $index => $segment ) {
			if ( $index === 0 ) {
				$camel[] = $segment;
			} else {
				$camel[] = ucfirst( $segment );
			}

		}

		return implode( '', $camel );
	}

	public static function guessDelimiter( ?string $string ): string {
		return Str::contains( $string, [' ', '-', '_', '/', '\\', ':', ';'] );
	}

	public static function containsValidHTML( ?string $string, ?string $mustContain = null ): string | bool {
		// debug( $html );
		if ( ! $string || ( str_starts_with( $string, '<' ) && ! str_ends_with( $string, '>' ) ) ) {
			return false;
		}

		if ( $mustContain && ! str_contains( $string, $mustContain ) ) {
			return false;
		}

		preg_match_all( '#<(?!meta|img|br|hr|input\b)\b([a-z]+)(?: .*)?(?<![/| ])>#iU', $string, $result );
		$openedTags = $result[1];
		preg_match_all( '#</([a-z]+)>#iU', $string, $result );
		$closedTags = $result[1];
		$len_opened = count( $openedTags );
		if ( count( $closedTags ) == $len_opened ) {
			return $string;
		}

		return false;
	}

	/** Generate URL-safe ID from string
	 *
	 * * `$trim` calculates a random sequence from the full hash
	 *
	 * @param string|null $input String to convert
	 * @param int         $trim  Length of the returned hash
	 * @param bool        $lower Return only lowercase
	 * @return string
	 */
	public static function hash( ?string $input = null, int $trim = 8, bool $lower = false ): string {
		$input ??= srand( hrtime()[1] ) . rand( 1, 128 );

		$int  = crc32( $input );
		$hash = base64_encode( hash( 'sha256', $input, true ) );
		$out  = $hash;

		if ( $trim ) {
			srand( $int );
			$max    = strlen( $out ) - $trim;
			$offset = rand( 0, $max );
			$out    = substr( $out, $offset, $trim );
		}

		if ( $lower === true ) {
			return strtolower( $out );
		}

		return $out;
	}
}