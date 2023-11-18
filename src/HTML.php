<?php

namespace Northrook\Support;

use Element;

final class HTML {
	
	public static function render( string $html ) : string {
		return $html;
	}
	
	public static function element( string $tag, ?string $innerHTML, array $attributes = [] ) : string {
		return implode( ' ', [
			"<$tag",
			Element::attributes( $attributes ),
			'>',
			$innerHTML ?? '',
			'</' . $tag . '>',
		] );
	}
}