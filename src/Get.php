<?php

namespace Northrook\Support;

final class Get {
	
	
	/** Extract numbers from a string, or an array of strings
	 *
	 * @todo [low] Add support for arrays
	 *
	 * @param int|float|string|array|null	$string
	 * @param bool							$returnArray
	 *
	 * @return float|int|array
	 */
	public static function numbers( int | float | string | array | null $string, bool $returnArray = false ) : float | int | array {
		
		if ( is_int( $string ) || is_float( $string ) ) return $string;
		preg_match_all( '/\b\d+(\.\d+)?\b/', $string, $matches );
		
		$matches = array_map( 'floatval', $matches[ 0 ] );
		
		if ( $returnArray ) return $matches;
		
		return (float) implode( '', $matches );
		
		// if ( ! $as_object ) return (int) preg_replace( '/[^0-9]/', '', $string );
		//
		// preg_match_all( '/[0-9]+/', $string, $numbers );
		//
		// return (object) $numbers[ 0 ];
	}
	
}