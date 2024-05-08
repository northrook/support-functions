<?php

namespace Northrook\Support\HTML;

use DOMDocument;
use Northrook\Support\Str;

trait ExtractHtmlAttributesTrait
{

    public static function extractAttributes( string $html ) : array {

        if ( !$html ) {
            return [];
        }

        $html = Str::squish( $html );

        if ( false === str_starts_with( $html, '<' ) && false === str_starts_with( $html, '>' ) ) {
            $html = "<div $html>";
        }
        else {
            $html = strstr( $html, '>', true ) . '>';
            $html = preg_replace(
                pattern     : '/^<(\w.+):\w+? /',
                replacement : '<$1 ',
                subject     : $html,
            );
        }

        $tag ??= substr( $html, 1, strpos( $html, ' ' ) - 1 );
        $dom = new DOMDocument();
        $dom->loadHTML( $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOERROR );

        $attributes = [];

        $node = $dom->getElementsByTagName( $tag )->item( 0 );

        if ( !$node ) {
            return $attributes;
        }

        foreach ( $node->attributes as $attribute ) {
            $attributes[ $attribute->nodeName ] = $attribute->nodeValue;
        }

        return $attributes;
    }
}