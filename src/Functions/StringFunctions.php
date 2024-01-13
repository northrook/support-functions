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
}
