<?php

namespace Northrook\Support\Functions;

trait ArrayFunctions
{


	// TODO [low] Add option for match any, match all, and match none.
	public static function keyExists( mixed $array, array $keys ) : bool {

		if ( false === is_array( $array ) ) {
			return false;
		}

		foreach ( $keys as $key ) {
			if ( !array_key_exists( $key, $array ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * @param  array  $array
	 * @param  mixed  $value
	 * @param  null|string  $condition  = 'contains' | 'startsWith' | 'endsWith'
	 * @return bool
	 */
	public static function has( array $array, mixed $value, ?string $condition = 'contains' ) : bool | string {

		if ( !$array ) {
			return false;
		}

		foreach ( $array as $item ) {
			if ( $condition === 'contains' && strpos( $item, $value ) !== false ) {
				return $item;
			}
			else if ( $condition === 'startsWith' && str_starts_with( $item, $value ) ) {
				return $item;
			}
			else if ( $condition === 'endsWith' && str_ends_with( $item, $value ) ) {
				return $item;
			}
			else {
				if ( $item === $value ) {
					return $item;
				}
			}
		}

		return false;

	}
}