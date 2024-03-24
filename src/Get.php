<?php

namespace Northrook\Support;

use Northrook\Support\Config\Stopwords;
use Northrook\Support\HTML\Element;

class Get extends Make
{

    public static function className( ?object $class = null ) : string {
        $class = is_object( $class ) ? get_class( $class ) : $class;
        return substr( $class, strrpos( $class, '\\' ) + 1 );
    }


    public static function element(
        string $tag,
        array  $attributes = [],
        array  $content = [],
        bool   $compress = false,
        bool   $pretty = false,
        bool   $parseTemplate = false,
        ?bool  $close = null,
    ) : string {

        return (string) new Element(
            $tag,
            $attributes,
            $content,
            $compress,
            $pretty,
            $parseTemplate,
            $close
        );
    }

    /**
     * @TODO [mid] Add $class support
     *
     *
     * @param string|null  $get
     * @param string|null  $pack
     * @param string|null  $class
     * @param float|null   $stroke
     *
     * @return string|null
     */
    public static function icon(
        ?string $get,
        ?string $pack = null,
        ?string $class = null,
        ?float  $stroke = null,
        bool    $raw = false,
        bool    $xmlns = false,
    ) : ?string {

        die( debug_backtrace() );

        if ( !$get ) {
            return null;
        }

        $get  = array_filter( explode( ':', $get ) );
        $icon = [
            'name' => $get[ 0 ] ?? null,
            'pack' => $get[ 1 ] ?? $pack ?? 'lucide',
        ];

        $path = Str::filepath( Get::config()->iconsDir . '/' . $icon[ 'pack' ] . '/' . $icon[ 'name' ] . '.svg' );
        if ( !file_exists( $path ) ) {
            return null;
        }

        $icon = file_get_contents( $path );

        if ( !$xmlns ) {
            $icon = str_replace( ' xmlns="http://www.w3.org/2000/svg"', '', $icon );
        }
        $stroke ??= 1.5;
        $icon   = preg_replace( '/ stroke-width=".*?"/', " stroke-width=\"$stroke\"", $icon );
        if ( $raw ) {
            return $icon;
        }

        return "<i class=\"icon\">$icon</i>";
    }

    public static function stopwords( string $group = 'en' ) : array {
        return Stopwords::get( $group );
    }

}