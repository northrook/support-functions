<?php

namespace Northrook\Support\HTML;

use Northrook\Support\Str;
abstract class Render {
	
	public static function innerHTML( string | array | null $content = null, bool $parseTemplate = false ) : ?string {
		if ( is_array( $content ) ) $content = implode( '', $content );
		if ( $parseTemplate ) $content = Render::template( $content );
		return $content ? trim( $content ) : null;
	}
	
	/** Render a Core template
	 *
	 * @TODO [low] Implement direct string cache option
	 * @TODO [low] Implement add link to docs
	 *
	 * @param string|null	$template
	 * @param array			$data
	 *
	 * @return string|null
	 */
	public static function template( ?string $template, array $data = [] ) : ?string {
		if ( ! $template ) return null;
		if ( ! Str::containsAll( $template, [ '{{', '}}' ] ) ) return $template;
		return preg_replace_callback(
			'/{{\s*+(\w.+?)\s*+}}/',
			static function( $matches ) use ( $data ) {
				$key = $matches[ 1 ];
				return $data[ $key ] ?? null;
			},
			$template,
		);
	}
}