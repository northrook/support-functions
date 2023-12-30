<?php

namespace Northrook\Support;

class Format {

	/**
	 * * Spacing, XX XX XX XX, XXXX XXX XXX etc
	 * * Areacode detector, 00XX, +XX etc
	 *
	 * @param string $number
	 * @return string
	 */
	public static function telephone( string $number ): string {
		return preg_replace( '/[^0-9]/', '', $number );
	}

	public static function nl2span( string $string, string $whitespace = " " ): string {
		$string = str_replace( ['<p>', '</p>'], ['<span>', '</span>'], $string );
		$array  = Arr::explode( PHP_EOL, $string );

		return Arr::implode( $array, wrap: 'span' );
	}

	public static function nl2p( string $string, string $whitespace = " " ): string {
		// $string = str_replace( [ '<p>', '</p>', ], [ '<span>', '</span>', ], $string );
		$explode = Arr::explode( "\n", $string );

        
		return Arr::implode( $explode, wrap: 'p' );
	}

	public static function nl2Auto( string $string, string $whitespace = " " ): string {

		$array = Arr::explode( "\n", $string );

        if ( empty($array)) {
            $wrap = 'span';
        }

        if ( count( $array ) === 1 ) {
            return "<span>$string</span>";
        }
        
		return Arr::implode( $array, wrap: 'p' );
	}
}