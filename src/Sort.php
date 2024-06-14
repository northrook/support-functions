<?php

declare( strict_types = 1 );

namespace Northrook\Support;


/**
 * @author  Martin Nielsen <mn@northrook.com>
 */
final class Sort
{
    // TODO Sort by partial name, e.g. 'data-' and 'aria-' etc
    public static function elementAttributes(
        array  $attributes,
        ?array $order = null,
        ?array $sortByList = null,
    ) : array {

        $sortByList ??= [
            'id',
            'href',
            'src',
            'rel',
            'name',
            'type',
            'value',
            'class',
            'style',
        ];
        $sort       = [];

        foreach ( $order ?? $sortByList as $value ) {
            if ( array_key_exists( $value, $attributes ) ) {
                $sort[ $value ] = $attributes[ $value ];
            }
        }

        return array_merge( $sort, $attributes );
    }


    /**
     * TODO Refactor this, find out exactly how PHP sorting algorithms function
     *    ? Is the returned int a weighted relative order, or boolean?
     *    ? Do we need to flip, I assume we do so to deduplicate the array?
     *
     * @param                $a
     * @param                $b
     * @param array|null     $sortByList
     *
     * @return int
     */
    public static function stylesheetDeclarations( $a, $b, ?array $sortByList = null ) : int {

        $sortByList ??= [
            'content',
            'order',
            'position',
            'z-index',
            'inset',
            'top',
            'right',
            'bottom',
            'left',
            'float',
            'clear',

            // Display
            'display',
            'flex',
            'flex-flow',
            'flex-basis',
            'flex-direction',
            'flex-grow',
            'flex-shrink',
            'flex-wrap',
            'justify-content',
            'align-content',
            'align-items',
            'align-self',
            'gap',
            'column-gap',
            'row-gap',
            'grid-template-columns',

            // Box
            'height',
            'min-height',
            'max-height',
            'width',
            'min-width',
            'max-width',
            'max-inline-size',
            'margin',
            'margin-top',
            'margin-right',
            'margin-bottom',
            'margin-left',
            'padding',
            'padding-top',
            'padding-right',
            'padding-bottom',
            'padding-left',
            'box-sizing',
            'block-size',
            'overflow',
            'overflow-x',
            'overflow-y',
            'scroll-behavior',
            'scroll-padding-top',

            // Text
            'color',
            'font',
            'font-family',
            'font-size',
            'font-weight',
            'font-style',
            'font-variant',
            'font-size-adjust',
            'font-stretch',
            'text-align',
            'text-align-last',
            'text-justify',
            'vertical-align',
            'white-space',
            'text-decoration',
            'text-emphasis',
            'text-emphasis-color',
            'text-emphasis-style',
            'text-emphasis-position',
            'text-indent',
            'text-rendering',
            'line-height',
            'letter-spacing',
            'word-spacing',
            'text-outline',
            'text-transform',
            'text-wrap',
            'text-overflow',
            'text-overflow-ellipsis',
            'text-overflow-mode',
            'word-wrap',
            'word-break',
            'tab-size',
            'hyphens',
            'text-size-adjust',
            '-webkit-text-size-adjust',
            '-webkit-font-smoothing',
            '-webkit-tap-highlight-color',
            'border',
            'border-width',
            'border-style',
            'border-color',
            'border-top',
            'border-top-width',
            'border-top-style',
            'border-top-color',
            'border-right',
            'border-right-width',
            'border-right-style',
            'border-right-color',
            'border-bottom',
            'border-bottom-width',
            'border-bottom-style',
            'border-bottom-color',
            'border-left',
            'border-left-width',
            'border-left-style',
            'border-left-color',
            'border-radius',
            'border-top-left-radius',
            'border-top-right-radius',
            'border-bottom-right-radius',
            'border-bottom-left-radius',
            'border-image',
            'border-image-source',
            'border-image-slice',
            'border-image-width',
            'border-image-outset',
            'border-image-repeat',
            'outline',
            'outline-width',
            'outline-style',
            'outline-color',
            'outline-offset',
            'background',
            'background-color',
            'background-image',
            'background-repeat',
            'background-attachment',
            'background-position',
            'background-position-x',
            'background-position-y',
            'background-clip',
            'background-origin',
            'background-size',
            'box-decoration-break',
            'box-shadow',
            'text-shadow',

            // Appearance
            '-webkit-appearance',
            'appearance',
            '',
            '',
            'cursor',
            'user-select',
            'pointer-events',
            'table-layout',
            'empty-cells',
            'caption-side',
            'border-spacing',
            'border-collapse',
            'list-style',
            'list-style-position',
            'list-style-type',
            'list-style-image',
            'quotes',
            'counter-reset',
            'counter-increment',
            'resize',
            'nav-index',
            'nav-up',
            'nav-right',
            'nav-down',
            'nav-left',
            'transform',
            'transform-origin',
            'visibility',
            'opacity',
            'clip',
            'fill',
            'zoom',
            'transition',
            'transition-delay',
            'transition-timing-function',
            'transition-duration',
            'transition-property',
            'animation',
            'animation-name',
            'animation-duration',
            'animation-play-state',
            'animation-timing-function',
            'animation-delay',
            'animation-iteration-count',
            'animation-direction',
            'animation-fill-mode',
        ];

        $order = 0;

        if ( !$b ) {
            return $order;
        }


        $hierarchy = array_flip( $sortByList );
        $a         = trim( $a, ' :' );
        $b         = trim( $b, ' :' );
        if (
            array_key_exists( $a, $hierarchy )
            &&
            array_key_exists( $b, $hierarchy )
        ) {
            $order = $hierarchy[ $a ] <=> $hierarchy[ $b ];
        }

        return $order;
    }
}