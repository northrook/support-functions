<?php

namespace Northrook\Support;

class Num
{

    /** Extract numbers from a string, or an array of strings
     *
     * @param int|float|string|array|null  $from
     * @param bool                         $returnArray
     *
     * @return float|int|array
     * @todo [low] Add support for arrays
     *
     */
    public static function extract( int | float | string | array | null $from, bool $returnArray = false,
    ) : float | int | array {

        if ( is_int( $from ) || is_float( $from ) ) {
            return $from;
        }

        preg_match_all( '/\b\d+(\.\d+)?\b/', $from, $matches );

        $matches = array_map( 'floatval', $matches[ 0 ] );

        if ( $returnArray ) {
            return $matches;
        }

        return (float) implode( '', $matches );
    }

    public static function inRange( int $value, int $min, int $max ) : bool {
        return $value >= $min && $value <= $max;
    }

    public static function intWithin( int $value, float $ceil, float $floor ) : int {
        return match ( true ) {
            $value >= $ceil => $ceil,
            $value < $floor => $floor,
            default         => $value
        };

    }

    public static function withinTolerance(
        int  $data,
        int  $tolerance,
        int  $compare,
        bool $invert = false,
        int  $override_tolerance = null,
    ) : bool {
        $plus  = $compare + $tolerance;
        $minus = $compare - $tolerance;
        // debug( $data - $compare, 2, 'tolerance ±' . $tolerance );
        // debug( 'high ' . $plus, 2, 'low ' . $minus );

        return ( $invert ) ? ( $data < $plus || $data > $minus ) : ( $data > $plus || $data < $minus );

        // if ( $override_tolerance ) {
        //
        // 	$plus     = $compare + $override_tolerance;
        // 	$minus    = $compare - $override_tolerance;
        // 	$override = ( $invert ) ? ( $data < $plus || $data > $minus ) : ( $data > $plus || $data < $minus );
        //
        // 	if ( $override ) {
        //
        // 		debug( 'true', 2, 'override' );
        // 	}
        // 	else {
        // 		debug( 'false', 2, 'override' );
        // 	}
        // }

        // if ( $pass ) {
        // 	debug( 'true', 2, 'interval' );
        //
        // 	return true;
        // }
        //
        // debug( 'false', 2, 'interval' );
        //
        // return false;
    }

    /**
     *
     * @link https://stackoverflow.com/questions/5464919/find-a-matching-or-closest-value-in-an-array stackoverflow
     *
     * @param int    $match
     * @param array  $array
     * @param bool   $returnKey
     *
     * @return mixed
     */
    public static function closest( int $match, array $array, bool $returnKey = false ) : mixed {

        foreach ( $array as $key => $value ) {
            if ( $match <= $value ) {
                return $returnKey ? $key : $value;
            }

        }

        return null;
    }

}