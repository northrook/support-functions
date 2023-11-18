<?php

namespace Northrook\Support\HTML;

final class Element {
	
	public function __construct(
		public string $tag,
		public ?string $innerHTML = null,
		public array $attributes = [],
	) {}
	
	public function __toString() : string {
		return implode( ' ', [
			"<$this->tag",
			Element::attributes( $this->attributes ),
			'>',
			$this->innerHTML ?? '',
			'</' . $this->tag . '>',
		] );
	}
	
	public static function attributes( array $jit, ?array $default = [] ) : string {
		$attributes = [];
		foreach ( $default + $jit as $key => $value ) {
			
			// TODO Flatten sub-arrays
			if ( is_array( $value ) ) $value = implode( ' ', array_filter( $value ) );
			
			$attributes[] = $key . ( $value ? '="' . $value . '"' : '' );
		}
		return implode( ' ', $attributes );
	}
}