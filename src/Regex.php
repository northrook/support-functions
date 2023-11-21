<?php
/*
 * Copyright (c) 2023. Lorem ipsum dolor sit amet, consectetur adipiscing elit.
 * Morbi non lorem porttitor neque feugiat blandit. Ut vitae ipsum eget quam lacinia accumsan.
 * Etiam sed turpis ac ipsum condimentum fringilla. Maecenas magna.
 * Proin dapibus sapien vel ante. Aliquam erat volutpat. Pellentesque sagittis ligula eget metus.
 * Vestibulum commodo. Ut rhoncus gravida arcu.
 */

namespace Northrook\Support;

final class Regex {
	
	/**
	 * @param string		$pattern
	 * @param string|null	$string
	 * @param string|null	$flag	s | Regex match flags
	 * @param string|bool	$trim	TODO Idea is to pass characters to stop from each matched string
	 *
	 * @return object
	 * @uses preg_match_all() $match, $string, PREG_SET_ORDER
	 */
	public static function matchNamedGroups(
		string $pattern,
		?string $string,
		string | bool $trim = false
	) : object {
		$tag		= [];
		$matches	= preg_match_all(
			$pattern,
			$string,
			$captured,
			PREG_SET_ORDER
		);
		if ( ! $matches ) {
			return Arr::asObject( $tag );
		}
		
		foreach ( $captured as $matched ) {
			$element	= [ 'matched' => $matched[ 0 ] ];
			$element	+= array_filter(
				$matched,
				static fn( $k ) => is_string( $k ),
				ARRAY_FILTER_USE_KEY
			);
			
			if ( $trim ) {
				$element = array_map(
					static fn( $value ) => trim(
						$value, ( $trim === true ) ? " \t\n\r\0\x0B" : $trim
					),
					$element );
			}
			$tag[] = $element;
		}
		
		return Arr::asObject( $tag );
	}
	
}