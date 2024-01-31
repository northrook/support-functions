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
	function mb_substr( string $string, int $start = 0, ?int $length = null ): string {
		return substr( $string, $start, $length );
	}
}

if ( ! function_exists( 'mb_stripos' ) ) {
	/**
	 * Fallback for mb_stripos
	 */
	function mb_stripos( string $haystack, string $needle, int $offset = 0 ): string {
		return stripos( $haystack, $needle, $offset );
	}
}

trait StringFunctions {

	/** Determine if a $string starts with any $substrings.
	 *
	 * * Case Insensitive by default
	 *
	 * @param  ?string  $string
	 * @param  string|array $substrings
	 * @param  bool     $caseSensitive
	 * @return bool
	 */
	public static function startsWith(
			?string $string,
		string | array $substrings,
		bool $caseSensitive = false
	): bool {
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

	/** Determine if a $string ends with any $substrings.
	 *
	 * * Case Insensitive by default
	 *
	 * @param  ?string  $string
	 * @param  string|array $substrings
	 * @param  bool     $caseSensitive
	 * @return bool
	 */
	public static function endsWith(
			?string $string,
		string | array $substrings,
		bool $caseSensitive = false
	): bool {
		foreach ( (array) $substrings as $substring ) {
			if ( str_ends_with(
				$string,
				$caseSensitive ? $substring : mb_strtolower( $substring ),
			) ) {
				return true;
			}
		}

		return false;
	}

	/** Returns a $string that starts $with a certain substring.
	 *
	 * * Passed null values will be stringified before comparison
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

	/** Returns a $string that ends $with a certain substring.
	 *
	 * * Passed null values will be stringified before comparison
	 * * Avoid `$trim` when used in loops
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

	/** Returns a substring after the first or last occurrence of a $needle in a $string.
	 * @param string $string
	 * @param string $needle
	 * @param bool $last
	 * @param bool $strict
	 * @return null|string
	 */
	public static function after(
		string $string,
		string $needle,
		bool $last = false,
		bool $strict = false
	): ?string {

		if ( $last ) {
			$needle = strrpos( $string, $needle );
		} else {
			$needle = strpos( $string, $needle );
		}

		if ( $strict && $needle === false ) {
			return null;
		}

		if ( $needle !== false ) {
			return substr( $string, $needle + 1 );
		}

		return $string;
	}

	/** Returns a substring before the first or last occurrence of a $needle in a $string.
	 * @param string $string
	 * @param string|array $match
	 * @param bool $last
	 * @return null|string
	 */
	public static function before(
		string $string,
		string | array $match,
		bool $last = false
	): ?string {

		$needles = [];
		foreach ( (array) $match as $value ) {
			if ( $last ) {
				$needle = strrpos( $string, $value );
			} else {
				$needle = strpos( $string, $value );
			}

			if ( $needle !== false ) {
				$needles[] = $needle;
			}
		}

		if ( empty( $needles ) ) {
			return $string;
		}

		$needle = $last ? max( $needles ) : min( $needles );

		return substr( $string, 0, $needle );
	}
}
