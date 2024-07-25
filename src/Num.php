<?php /** @noinspection SpellCheckingInspection */

namespace Northrook\Support;

use JetBrains\PhpStorm\ExpectedValues;
use function Northrook\numberDecimals;

class Num
{
    private const UNITS = [
        'B',  //Bytes
        'kB',  //Kilobytes
        'MB',  //Megabytes
        'GB',  //Gigabytes
        'TB',  //Terabytes
        'PB',  //Petabytes
        'EB',  //Exabytes
        'ZB',  //Zettabytes
        'YB',  //Yottabytes
    ];

    /**
     * Return a variable as byte size in a human-readable format, or as a sized float.
     *
     * @param mixed   $bytes               The variable to be formatted
     * @param string  $to                  The unit to format to, defaults to 'KB'
     * @param int     $decimals            The number of decimal places to display, defaults to 2
     * @param bool    $forceDecimalValues  If true, will force $decimals number values to be displayed, regardless of leading zeros
     * @param bool    $returnFloat         If true, will return a float instead of a string
     *
     * @return float|string The formatted value, 1.00, 1KB, 1.00MB, etc.
     */
    public static function formatBytes(
        mixed  $bytes,
        #[ExpectedValues( Num::UNITS )]
        string $to = 'KB',
        int    $decimals = 2,
        bool   $forceDecimalValues = true,
        bool   $returnFloat = false,
    ) : float | string {

        if ( !\is_numeric( $bytes ) ) {
            $bytes = strlen( print_r( $bytes, true ) );
        }

        $bytes = (float) $bytes;

        $i    = 0;
        $unit = array_flip( Num::UNITS )[ $to ];

        while ( $i < 0 || $i !== $unit ) {
            $bytes /= 1024;
            $i++;
        }

        // If we have leading zeros
        if ( $bytes < 1 ) {
            $floating = substr( $bytes, 2 );
            $decimals += strlen( $floating ) - strlen( ltrim( $floating, '0' ) );
        }

        $number = numberDecimals( $bytes, $decimals );

        return $returnFloat ? $number : ltrim( $number, '0' ) . Num::UNITS[ $unit ];
    }

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

        if ( !\is_numeric( $from ) ) {
            return $from;
        }

        preg_match_all( '/\b\d+(\.\d+)?\b/', $from, $matches );

        $matches = array_map( 'floatval', $matches[ 0 ] );

        if ( $returnArray ) {
            return $matches;
        }

        return (float) implode( '', $matches );
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
}