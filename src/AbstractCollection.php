<?php

namespace Northrook\Support;

abstract class AbstractCollection  {

	/** Add properties to the class
	 *
	 * @param array $properties
	 * @param bool $strict If true, throws an exception if a property is not defined
	 * @throws \UnexpectedValueException
	 */
	public function __construct( array $properties = [], bool $strict = true ) {
		foreach ( $properties as $key => $value ) {
			if ( property_exists( $this, $key ) ) {
				$this->{$key} = $value;
			} else if ( $strict ) {
				throw new \UnexpectedValueException( 'The "' . $this::class . '" does not have the property "' . gettype( $key ) . ' $' . $key . '" defined.' );
			}
		}
	}
}