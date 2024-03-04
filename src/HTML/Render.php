<?php

namespace Northrook\Support\HTML;

use JetBrains\PhpStorm\Deprecated;
use Northrook\Support\Format;
use Northrook\Support\Str;

#[Deprecated]
abstract class Render
{

	public static function innerHTML(
		string | array | null $content = null,
		?string               $template = null,
	) : ?string {

		if ( $template ) {
			if ( !is_array( $content ) ) {
				throw new \Exception( 'Content must be an array when using a template.' );
			}
			$content = Render::template( $template, $content );
		}

		if ( is_array( $content ) ) {
			$content = implode( '', $content );
		}

		return $content ? trim( $content ) : null;
	}

	public static function element(
		string | array | null $content = null, bool $parseTemplate = false, bool $compress = true,
	) : ?string {
		if ( is_array( $content ) ) {
			$content = implode( ' ', $content );
		}

		if ( !$content ) {
			return null;
		}

		if ( $parseTemplate ) {
			$content = Render::template( $content );
		}

		return $compress ? Str::squish( $content ) : $content;
	}

	/**
	 * Render a Core template
	 *
	 * @TODO [low] Implement direct string cache option
	 * @TODO [low] Implement add link to docs
	 *
	 *
	 * @param  string|null  $template
	 * @param  array  $data
	 * @return string|null
	 */
	public static function template( ?string $template, array $data = [] ) : ?string {

		if ( !$template ) {
			return null;
		}

		if ( !Str::containsAll( $template, [ '{{', '}}' ] ) ) {
			return $template;
		}

		return preg_replace_callback(
			'/{{\s*+(\w.+?)\s*+}}/',
			static function ( $matches ) use ( $data ) {
				$key = $matches[ 1 ];
				$null = null;
				$fn = null;
				if ( str_contains( $key, ':' ) ) {
					$key = Str::split( $key, separator : ':' );
					$fn = $key[ 1 ];
					$key = $key[ 0 ];
				}

				if ( str_contains( $key, '??' ) ) {
					$key = Str::split( $key, separator : '??' );
					$null = $key[ 1 ];
					$key = $key[ 0 ];
				}

				$data = $data[ $key ] ?? $null;

				if ( $fn ) {

					if ( method_exists( Format::class, $fn ) ) {
						$data = Format::$fn( $data );
					}
					else if ( function_exists( $fn ) ) {
						$data = $fn( $data );
					}
					else {
						$data = "<$fn>$data</$fn>";
					}

				}

				return $data;
			},
			$template,
		);
	}
}