<?php

namespace Northrook\Support;

class Num {
	
	/** Extract numbers from a string, or an array of strings
	 *
	 * @todo [low] Add support for arrays
	 *
	 * @param int|float|string|array|null	$from
	 * @param bool							$returnArray
	 *
	 * @return float|int|array
	 */
	public static function extract( int | float | string | array | null $from, bool $returnArray = false ) : float | int | array {
		
		if ( is_int( $from ) || is_float( $from ) ) return $from;
		preg_match_all( '/\b\d+(\.\d+)?\b/', $from, $matches );
		
		$matches = array_map( 'floatval', $matches[ 0 ] );
		
		if ( $returnArray ) return $matches;
		
		return (float) implode( '', $matches );
	}
	
	public static function inRange( int $value, int $min, int $max ) : bool {
		return $value >= $min && $value <= $max;
	}
	
	public static function intWithin( int $value, float $ceil, float $floor ) : int {
		return match ( true ) {
			$value >= $ceil	=> $ceil,
			$value < $floor	=> $floor,
			default			=> $value
		};
	}
	
	/**
	 *
	 * @link https://stackoverflow.com/questions/5464919/find-a-matching-or-closest-value-in-an-array stackoverflow
	 *
	 * @param int	$match
	 * @param		$array
	 * @param bool	$returnKey
	 *
	 * @return mixed
	 */
	public static function closest( int $match, array $array, bool $returnKey = false ) : mixed {
		
		foreach ( $array as $key => $value ) {
			if ( $match <= $value ) return $returnKey ? $key : $value;
		}
		
		return null;
	}
	
}