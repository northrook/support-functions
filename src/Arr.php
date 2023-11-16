<?php

namespace Northrook\Support;

/**
 * TODO [low] Integrate features from https://github.com/adbario/php-dot-notation/blob/3.x/src/Dot.php
 *   ✅ Dot notation
 */
final class Arr {
	
	private mixed $array = [];
	
	public static function dot( mixed $items, bool $returnThis = false, string $delimiter = ':' ) : array | Arr {
		$dot = new Arr( $items, $delimiter );
		return $returnThis ? $dot : $dot->array;
	}
	
	/**
	 * Create a new Dot instance
	 *
	 * @param mixed				$items
	 * @param non-empty-string	$delimiter
	 *
	 * @return void
	 */
	private function __construct( mixed $items, readonly private string $delimiter = ':' ) {
		$this->set( $this->getArrayItems( $items ) );
	}
	
	/**
	 * Return the given items as an array
	 *
	 * @param mixed $items
	 *
	 * @return array
	 */
	protected function getArrayItems( mixed $items ) : array {
		if ( is_array( $items ) ) return $items;
		
		if ( $items instanceof self ) return $items->array;
		
		return (array) $items;
	}
	
	public function all() : array {
		return $this->array;
	}
	
	
	/**
	 * Set a given key / value pair or pairs
	 *
	 * @param int|array|string	$keys
	 * @param mixed|null		$value
	 *
	 * @return $this
	 * */
	public function set( int | array | string $keys, mixed $value = null ) : Arr {
		if ( is_array( $keys ) ) {
			foreach ( $keys as $key => $array ) {
				$this->set( $key, $array );
			}
			return $this;
		}
		
		$items = &$this->array;
		
		if ( is_string( $keys ) ) {
			foreach ( explode( $this->delimiter, $keys ) as $key ) {
				if (
					! isset( $items[ $key ] )
					|| ! is_array( $items[ $key ] )
				) {
                    $items[ $key ] = [];
				}
				
				$items = &$items[ $key ];
			}
		}
		
		$items = $value;
		
		return $this;
	}
	
	
	/**
	 * Return the value of a given key
	 *
	 * @param int|string|null	$key
	 * @param mixed				$default
	 *
	 * @return mixed
	 */
	public function get( $key = null, mixed$default = null ) : mixed {
		if ( $key === null ) return $this->array;
		
		
		if ( array_key_exists( $key, $this->array ) ) {
			return $this->array[ $key ];
		}
		
		if ( ! is_string( $key ) || ! str_contains( $key, $this->delimiter ) ) {
			return $default;
		}
		
		$items = $this->array;
		
		foreach ( explode( $this->delimiter, $key ) as $segment ) {
			if ( ! is_array( $items ) || ! array_key_exists( $segment, $items ) ) {
				return $default;
			}
			
			$items = &$items[ $segment ];
		}
		
		return $items;
	}
	
	/**
	 * Set a given key / value pair or pairs
	 * if the key doesn't exist already
	 *
	 * @param array|int|string	$keys
	 * @param mixed				$value
	 *
	 * @return $this
	 */
	public function add( array | int | string $keys, mixed $value = null ) : Arr {
		if ( is_array( $keys ) ) {
			foreach ( $keys as $key => $array ) $this->add( $key, $array );
		}
		elseif ( $this->get( $keys ) === null ) {
			$this->set( $keys, $value );
		}
		
		return $this;
	}
	
	
	public static function update( array $array, array ...$arrays ) : array {
		return self::merge_nested( $array, ...$arrays );
	}
	
	public static function merge_nested( array ...$arrays ) : array {
		$merged = [];
		foreach ( $arrays as $array ) {
			
			if ( ! is_array( $array ) ) continue;
			
			foreach ( $array as $key => $value) {
				if ( isset( $merged[ $key ] )
                    && is_array( $value )
                    && is_array( $merged[ $key ] )
				) {
                    $merged[ $key ] = self::merge_nested( $merged[ $key ], $value );
				}
				else $merged[ $key ] = $value;
			}
		}
		
		return $merged;
	}
}