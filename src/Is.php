<?php

namespace Northrook\Support;

class Is {

	public static function email( mixed $value ): string | bool {
		if ( ! filter_var( $value, FILTER_VALIDATE_EMAIL ) ) {
			return false;
		}
		return trim( $value );
	}
}