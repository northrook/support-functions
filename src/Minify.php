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
	
}