<?php

namespace Northrook\Support\HTML;

use DOMDocument;
use DOMNode;
use Northrook\Support\Sort;
use Northrook\Support\Str;
use Northrook\Support\UserAgent;

class Element extends Render {

	public ?string $innerHTML = null;

	/**
	 * List of generated element IDs
	 *
	 * @var array
	 */
	private static array $generatedElementIdList = [];
	private readonly bool $close;

	/**
	 * Get a list of generated element IDs
	 *
	 *  Useful for preventing duplicate IDs
	 *
	 * @return array
	 */
	public static function getGeneratedIdList(): array {
		return Element::$generatedElementIdList;
	}

	/**
	 * Create a new HTML Element
	 *
	 * @param string            $tag
	 * @param string|array|null $content       Note: HTML is escaped
	 * @param array             $attributes
	 * @param bool              $compress      Compress the HTML with Str::squish
	 * @param bool              $pretty        Pretty print the HTML, overrides $compress
	 * @param bool              $parseTemplate Run the $content through Render::template
	 */
	public final function __construct(
		public string $tag,
		public array $attributes = [],
		string | array | null $content = null,
		private readonly bool $compress = false,
		private readonly bool $pretty = false,
		private readonly ?string $template = null,
			?bool $close = null
	) {

		if ( $this->tag === 'button' && ! isset( $this->attributes['type'] ) ) {
			$this->attributes['type'] = 'button';
		}

		$this->close = $close ?? ! in_array( $tag, [
			'area', 'base', 'br', 'col', 'embed', 'hr', 'img', 'input', 'keygen', 'link', 'meta', 'param', 'source', 'track', 'wbr',
		] );

		if ( $content ) {
			$this->innerHTML = Element::innerHTML( $content, $this->template );
		}
	}

	/**
	 * Get the HTML, parsing $innerHTML and $attributes
	 *
	 * @todo [low] Implement static cache function, potentially as a method of Northrook\Core\Render as wrapper
	 *       This may be irrelevant, if we parse Latte templates at compile time
	 *
	 * @return string
	 */
	public function __toString(): string {
		$element = array_filter( ["$this->tag", Element::attributes( $this->attributes )] );
		$html    = '<' . implode( ' ', $element ) . '>';

		if ( $this->innerHTML ) {
			$html .= $this->innerHTML;
		}

		if ( $this->close ) {
			$html .= '</' . $this->tag . '>';
		}

		if ( $this->pretty ) {
			return PrettyHTML::string( $html );
		}

		return $this->compress ? Str::squish( $html ) : $html;
	}

	/**
	 *
	 * @param  array      $jit
	 * @param  array|null $default
	 * @return string
	 */
	public static function attributes( array $jit, ?array $default = [] ): string {

		$attributes = [];

		foreach ( $default + $jit as $attribute => $value ) {

			$attribute = Str::key( string: $attribute, separator: '-' );

			$value = match ( $attribute ) {
				'id', 'for' => Element::id( $value ),
				'class'     => Element::classes( $value ),
				'style'     => Element::styles( $value ),
				default     => $value
			};

			// if ( $attribute === 'id' ) {
			// 	$value = Element::id( $value );
			// }

			// if ( $attribute === 'class' ) {
			// 	$value = Element::classes( $value );
			// }

			// if ( $attribute === 'style' ) {
			// 	$value = Element::styles( $value );
			// }

			if ( in_array( $attribute, ['disabled', 'readonly', 'required', 'checked', 'hidden'] ) ) {
				if ( $value === true ) {
					$attributes[$attribute] = $attribute;
				} else {
					continue;
				}
			}

			if ( is_bool( $value ) ) {
				$value = $value ? 'true' : 'false';
			}

			if ( is_array( $value ) ) {
				$value = implode( ' ', array_filter( $value ) );
			}

			if ( $value !== null ) {
				if ( $attribute === $value ) {
					$attributes[$attribute] = $attribute;
				} else {
					$attributes[$attribute] = $attribute . ( $value !== null ? '="' . $value . '"' : '' );
				}
			}
		}

		$attributes = array_filter( $attributes );

		if ( empty( $attributes ) ) {
			return '';
		}

		return implode( ' ', Sort::elementAttributes( $attributes ) );
	}

	/**
	 * Get an element ID
	 *
	 *  The ID will be generated according to `Str::slug()` rules
	 *  The ID will be appended to Element::$generatedElementIdList
	 *
	 *
	 *
	 * @param  string|null $id
	 * @return ?string
	 */
	public static function id( ?string $id ): ?string {
		if ( Str::containsAll( $id, ['{', '}'] ) ) {
			return $id;
		}
		$id = Str::slug( $id );

		Element::$generatedElementIdList[] = $id;

		return $id;
	}

	/**
	 *
	 * @param  string|array|null $value Pass either a string or an array
	 * @return string|null
	 */
	public static function classes( string | array | null $value ): ?string {
		if ( ! $value ) {
			return null;
		}

		if ( is_string( $value ) ) {
			$value = explode( ' ', $value );
		}

		$classes = array_flip( array_flip( array_filter( $value ) ) );

		return strtolower( implode( ' ', $classes ) );
	}

	/**
	 * Parse element styles from $value and return a string
	 *
	 *
	 * @param  string|array|null $value
	 * @return string|null
	 */
	public static function styles( string | array | null $value ): ?string {
		if ( ! $value ) {
			return null;
		}

		if ( is_string( $value ) ) {
			$value = explode( ';', $value );
		}

		$styles = [];
		foreach ( array_filter( $value ) as $style ) {
			$style             = explode( ':', $style, 2 );
			$styles[$style[0]] = implode( ':', $style );
		}

		return implode( '; ', $styles );
	}

	/// use Render::element(); instead, allow passing attributes
	public static function keybind( ?string $string, ?string $tag = 'kbd' ): ?string {
		if ( ! $string ) {
			return null;
		}

		if ( UserAgent::OS( 'apple' ) ) {
			$string = str_replace( ['ctrl', 'alt'], ['⌘', '⌥'], $string );
		}

		return "<$tag>$string</$tag>";
	}

	/// use Render::element(); instead, allow passing attributes
	public static function tooltip( ?string $string, ?string $placement = 'top' ): ?string {
		if ( ! $string ) {
			return null;
		}

		return "<tooltip>$string</tooltip>";
	}

	public static function extractAttributes( string $html, string $tag ): array {
		$dom = new DOMDocument();
		$dom->loadHTML( $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOERROR );

		$attributes = [];

		$node = $dom->getElementsByTagName( $tag )->item( 0 );

		foreach ( $node->attributes as $attribute ) {
			$attributes[$attribute->nodeName] = $attribute->nodeValue;
		}

		return $attributes;
	}

	public static function extractElements( string $html, string $tag ): array {
		$dom = new DOMDocument();
		$dom->loadHTML( $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOERROR );

		$elements = [];

		$nodes = $dom->getElementsByTagName( $tag );

		foreach ( $nodes as $node ) {
			$element            = $dom->saveHTML( $node );
			$elements[$element] = [];
			foreach ( $node->attributes as $attribute ) {
				$value                                    = $attribute->nodeValue === '' ? true : $attribute->nodeValue;
				$elements[$element][$attribute->nodeName] = $value;
			}
		}
		// dd( $elements );

		return $elements;
	}

	public static function loadHTML( string $html ): DOMDocument {
		$dom = new DOMDocument();
		$dom->loadHTML( $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOERROR );

		return $dom;
	}

	public static function nodeContent( ?DOMNode $node, DOMDocument $dom, bool $entityDecode = true, bool $revertEmptySelfClosing = true ): string {

		$childNodes = $node->childNodes;

		$innerHTML = '';
		foreach ( $childNodes as $child ) {
			$innerHTML .= $dom->saveHTML( $child );
		}

		$content = $entityDecode ? html_entity_decode( $innerHTML ) : $innerHTML;

		if ( $revertEmptySelfClosing && str_contains( $content, "<" ) ) {
			$content = preg_replace( '/<(\w.+?)>\W*?<\/\1>/ms', '<$1/>', $content );
		}

		return $content ??= $node->textContent;
	}
}