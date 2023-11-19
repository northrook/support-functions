<?php

namespace Northrook\Support;

final class Minify {
	
	/** Remove whitespace, newlines, and comments from $string
	 *
	 * @param string	$string
	 * @param bool		$preserveComments
	 *
	 * @return string
	 */
	public static function html( string $string, bool $preserveComments = true ) : string {
		return Str::squish( $string, $preserveComments, $preserveComments );
	}
	
	public static function styles( ?string $path, bool $stripComments = true, bool $compress = true ) : string {
		
		// Set a timer for performance testing
		// $build_timer    = - hrtime( true );
		// $style_size_raw = format::bytesize( strlen( $styles ) );
		// $source         = debug_backtrace( ! DEBUG_BACKTRACE_PROVIDE_OBJECT | DEBUG_BACKTRACE_IGNORE_ARGS, 2 )[ 1 ][ 'function' ];
		
		// Remove @charset
		$styles = preg_replace( '/@charset.+?;/', '', $path );
		
		// $strip_comments from $styles / default true
		if ( $stripComments ) {
			$styles	= preg_replace( '!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $styles );
			$styles	= str_replace( "\t", '', $styles );
			// $styles = str_replace( ", ", ",\n", $styles );
			$styles = preg_replace( '/\\s* : \\s*/', ' : ', $styles );
		}
		
		if ( ! $compress ) return $styles;
		
		// Remove unnecessary whitespace and trailing semicolon
		$styles	= preg_replace( '/\\s*{\\s*/', '{', $styles );
		$styles	= preg_replace( '/;?\\s*}\\s*/', '}', $styles );
		$styles	= preg_replace( '/\s+/', ' ', $styles );
		
		// Remove unnecessary whitespace before and after semicolons
		$styles = preg_replace( '/\\s*;\\s*/', ';', $styles );
		
		// Line breaks and tabs
		$styles = str_replace( [ "\r\n", "\r", "\n", "\t" ], '', $styles );
		
		// Single space after colons
		$styles = str_replace( [ ': ', ' :' ], ':', $styles );
		
		// Single space next to commas
		$styles = str_replace( [ ', ', ' ,' ], ',', $styles );
		
		// Single space around combinators
		$styles	= str_replace( [ ' > ', '> ', ' >' ], '>', $styles );
		$styles	= str_replace( [ ' ~ ', '~ ', ' ~' ], '~', $styles );
		$styles	= str_replace( [ '- ', '+ ' ], [ ' + ', ' - ' ], $styles );
		
		// Unnecessary unit
		$styles = str_replace( [ ' 0px', ' 0em', ' 0rem' ], ' 0', $styles );
		
		// var - need to if there's something before it
		$styles	= str_replace( '\wvar', ' var', $styles );
		$styles	= str_replace( '. var', '.var', $styles );
		$styles	= str_replace( '\- var', '\-var', $styles );
		
		// Unnecessary leading zero
		$styles = preg_replace( '/(\b0\.\b)|(var|url|calc)\([^)]*\){}/', '.', $styles );
		
		$styles = stripslashes( $styles );
		
		
		// $style_size_end = format::bytesize( strlen( $styles ) );
		// $build_timer    += hrtime( true );
		// $build_time     = number_format( round( $build_timer / 1e+6, 3 ), 3 ) . 'ms';
		// $build_log      = 'Minified in ' . $build_time . ' from ' . $style_size_raw . ' to ' . $style_size_end;
		
		// debug( $build_log, 2, $source );
		
		return trim( $styles );
	}
	
	public static function scripts( ?string $string, bool $stripComments = true, bool $compress = true ) : string {
		return $string;
	}
}