<?php

namespace Northrook\Support\Functions;

trait ArrayFunctions {

	/**
	 * @param array $array
	 * @param mixed $value
	 * @param null|string $condition = 'contains' | 'startsWith' | 'endsWith'
	 * @return bool
	 */
	public static function has( array $array, mixed $value, ?string $condition = 'contains' ): bool {

		if ( ! $array ) {
			return false;
		}

		if ( is_string( $value ) ) {

			foreach ( $array as $item ) {
				if ( $condition === 'contains' && strpos( $item, $value ) !== false ) {
					return true;
				} elseif ( $condition === 'startsWith' && str_starts_with( $item, $value ) ) {
					return true;
				} elseif ( $condition === 'endsWith' && str_ends_with( $item, $value ) ) {
					return true;
				}
			}
		}

		// Check if array contains value of any other type
		$containsOtherType = count( array_filter( $array, function ( $item ) use ( $value ) {
			return $item !== $value;
		} ) ) > 0;

		return ! empty( $partialMatch ) || $containsOtherType;

	}
}