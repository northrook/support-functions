<?php

namespace Northrook\Support;

abstract class AbstractCollection  {

	public function __construct( array $properties = [] ) {
		foreach ( $properties as $key => $value ) {
			$this->{$key} = $value;
		}
	}
}