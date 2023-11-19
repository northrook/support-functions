<?php

namespace Northrook\Support\HTML;

use Northrook\Support\Arr;
use Northrook\Support\Sort;
use Northrook\Support\Str;
final class Element {
	
	public ?string $innerHTML = null;
	
	/** List of generated element IDs
	 *
	 * @var array
	 */
	private static array $generatedElementIdList = [];
	
	/** Get a list of generated element IDs
	 *
	 * * Useful for preventing duplicate IDs
	 *
	 * @return array
	 */
	public static function getGeneratedIdList() : array {
		return Element::$generatedElementIdList;
	}
	
	
	/** Create a new HTML Element
	 *
	 * @param string			$tag
	 * @param string|array|null	$content Note: HTML is escaped
	 * @param array				$attributes
	 */
	public function __construct(
		public string $tag,
		string | array | null $content = null,
		public array $attributes = [],
		private readonly bool $compress = false,
		bool $parseTemplate = false,
	) {
		$this->innerHTML = Render::innerHTML( $content, $parseTemplate );
	}
	
	/** Get the HTML, parsing $innerHTML and $attributes
	 *
	 * @todo [low] Implement static cache function, potentially as a method of Northrook\Core\Render as wrapper
	 *
	 * @return string
	 */
	public function __toString() : string {
		$tag	= implode( ' ', [ "<$this->tag", Element::attributes( $this->attributes ) ] ) . '>';
		$html	= $tag . $this->innerHTML . '</' . $this->tag . '>';
		return $this->compress ? Str::squish( $html ) : $html;
	}
	
	/**
	 * @param array			$jit
	 * @param array|null	$default
	 *
	 * @return string
	 */
	public static function attributes( array $jit, ?array $default = [] ) : string {
		$attributes = [];
		foreach ( $default + $jit as $key => $value ) {
			
			$key = Str::key( string : $key, separator : '-' );
			
			if ( $key === 'id' ) $value = Element::id( $value );
			if ( $key === 'class' ) $value = Element::classes( $value );
			if ( $key === 'style' ) $value = Element::styles( $value );
			
			if ( in_array( $key, [ 'disabled', 'readonly', 'required' ] ) ) {
				$attributes[ $key ] = $key;
				continue;
			}
			
			if ( is_bool( $value ) ) $value = $value ? 'true' : 'false';
			if ( is_array( $value ) ) $value = implode( ' ', array_filter( $value ) );
			
			$attributes[ $key ] = $key . ( $value ? '="' . $value . '"' : '' );
		}
		return implode( ' ', Sort::elementAttributes( $attributes ) );
	}
	
	/** Get an element ID
	 *
	 * * The ID will be generated according to `Str::slug()` rules
	 * * The ID will be appended to Element::$generatedElementIdList
	 *
	 *
	 * @param string|null $id
	 *
	 * @return string
	 */
	public static function id( ?string $id ) : string {
		$id = Str::slug( $id );
		
		Element::$generatedElementIdList[] = $id;
		return $id;
	}
	
	/**
	 * @param string|array|null $value Pass either a string or an array
	 *
	 * @return string|null
	 */
	public static function classes( string | array | null $value ) : ?string {
		if ( ! $value ) return null;
		if ( is_string( $value ) ) $value = explode( ' ', $value );
		return strtolower( implode( ' ', array_filter( $value ) ) );
	}
	
	/** Parse element styles from $value and return a string
	 *
	 * @param string|array|null $value
	 *
	 * @return string|null
	 */
	public static function styles( string | array | null $value ) : ?string {
		if ( ! $value ) return null;
		if ( is_string( $value ) ) $value = explode( ';', $value );
		$styles = [];
		foreach ( array_filter( $value ) as $style ) {
			$style					= explode( ':', $style, 2 );
			$styles[ $style[ 0 ] ]	= implode( ':', $style );
		}
		return implode( '; ', $styles );
	}
}