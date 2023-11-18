<?php


final class Element {
	
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