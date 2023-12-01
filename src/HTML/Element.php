<?php

namespace Northrook\Support\HTML;

use DOMDocument;
use Northrook\Support\Sort;
use Northrook\Support\Str;
use Northrook\Support\UserAgent;

final class Element extends Render {

    public ?string $innerHTML = null;

    /**
     * List of generated element IDs
     *
     * @var array
     */
    private static array $generatedElementIdList = [];

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
    public function __construct(
        public string $tag,
        public array $attributes = [],
        string | array | null $content = null,
        private readonly bool $compress = false,
        private readonly bool $pretty = false,
        private readonly bool $parseTemplate = false,
    ) {
        $this->innerHTML = $content;
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
        $tag  = implode( ' ', ["<$this->tag", Element::attributes( $this->attributes )] ) . '>';
        $html = $tag . Element::innerHTML( $this->innerHTML, $this->pretty, $this->parseTemplate ) . '</' . $this->tag . '>';
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
        foreach ( $default + $jit as $key => $value ) {

            $key = Str::key( string: $key, separator: '-' );

            if ( $key === 'id' ) {
                $value = Element::id( $value );
            }

            if ( $key === 'class' ) {
                $value = Element::classes( $value );
            }

            if ( $key === 'style' ) {
                $value = Element::styles( $value );
            }

            if ( in_array( $key, ['disabled', 'readonly', 'required'] ) ) {
                $attributes[$key] = $key;
                continue;
            }

            if ( is_bool( $value ) ) {
                $value = $value ? 'true' : 'false';
            }

            if ( is_array( $value ) ) {
                $value = implode( ' ', array_filter( $value ) );
            }

            $attributes[$key] = $key . ( $value ? '="' . $value . '"' : '' );
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
     * @return string
     */
    public static function id( ?string $id ): string {
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

        return strtolower( implode( ' ', array_filter( $value ) ) );
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

    public static function extractAttributes( string $html, string $tag ): array {
        $dom = new DOMDocument();
        $dom->loadHTML( $html );

        $attributes = [];

        $node = $dom->getElementsByTagName( $tag )->item( 0 );
        
        foreach ( $node->attributes as $attribute ) {
            $attributes[$attribute->name] = $attribute->value;
        }

        return $attributes;
    }
}