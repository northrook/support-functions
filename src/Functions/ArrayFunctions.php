<?php

namespace Northrook\Support\Functions;

trait ArrayFunctions {

	/**
	 * @param array $array
	 * @param mixed $value
	 * @param null|string $condition = 'contains' | 'startsWith' | 'endsWith'
	 * @return bool
	 */
	public static function has( array $array, mixed $value, ?string $condition = 'contains' ): bool | string {

		if ( ! $array ) {
			return false;
		}

		foreach ( $array as $item ) {
			if ( $condition === 'contains' && strpos( $item, $value ) !== false ) {
				return $item;
			} elseif ( $condition === 'startsWith' && str_starts_with( $item, $value ) ) {
				return $item;
			} elseif ( $condition === 'endsWith' && str_ends_with( $item, $value ) ) {
				return $item;
			} else if ( $item === $value ) {
				return $item;
			}
		}

		return false;

	}
}