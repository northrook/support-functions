<?php

namespace Northrook\Support;

use Northrook\Core\App;
use Northrook\Core\Env;
use Northrook\Logger\Log;
use Northrook\Support\Str\StringTrimFunctions;

/**
 * @template HTML of non-empty-string
 * @template CSS of non-empty-string
 * @template JS of non-empty-string
 *
 * Class Minify
 *
 * @package Northrook\Support
 */
final class Minify
{

    use StringTrimFunctions;

    /**
     * Remove whitespace, newlines, and HTML comments from `$string`
     *
     * @param string<HTML>  $string
     * @param bool          $preserveComments
     *
     * @return string<HTML>
     */
    public static function html( string $string, bool $preserveComments = true ) : string {

        if ( !$preserveComments ) {
            $string = Minify::trimComments( $string, html : true );
        }

        return Minify::trimWhitespace( $string );
    }

    /**
     * Quick and dirty minification of CSS.
     *
     * @param string<CSS>  $string
     *
     * @return string<CSS>
     */
    public static function styles( string $string ) : string {

        // TODO : [?] We could potentially run this through the Stylesheet Generator

        $css = Minify::trimWhitespace(
            string         : Minify::trimComments( $string, single : true ),
            removeTabs     : true,
            removeNewlines : true,
        );


        // TODO : [low] Further optimisations:

        // TODO : Unnecessary unit
        // $styles = str_replace( [ ' 0px', ' 0em', ' 0rem' ], ' 0', $styles );

        //? Unnecessary leading zero
        $css = preg_replace( '/(\b0\.\b)|(var|url|calc)\([^)]*\){}/', '.', $css );

        $css = preg_replace( '#\s+([:;,.>~])\s+?#', '$1', $css );

        if ( Env::isDevelopment() ) {

            $fromKB = Num::formatBytes( $string, 'kB', returnFloat : true );
            $toKB   = Num::formatBytes( $css, 'kB', returnFloat : true );
            $diffKB = $fromKB - $toKB;

            Log::Notice(
                message : 'CSS string minified. {from} to {to}, saving {diff}',
                context : [
                              'from'     => "{$fromKB}KB",
                              'to'       => "{$toKB}KB",
                              'diff'     => "{$diffKB}KB",
                              'original' => $string,
                              'minified' => $css,
                          ],
            );
        }

        return $css;
    }

    public static function deprecatedStylesMinifier(
        ?string $path,
        bool    $stripComments = true,
        bool    $compress = true,
    ) : string {

        // Remove @charset
        $styles = preg_replace( '/@charset.+?;/', '', $path );

        // $strip_comments from $styles / default true
        if ( $stripComments ) {
            $styles = preg_replace( '!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $styles );
            $styles = str_replace( "\t", '', $styles );
            // $styles = str_replace( ", ", ",\n", $styles );
            $styles = preg_replace( '/\\s* : \\s*/', ' : ', $styles );
        }

        if ( !$compress ) {
            return $styles;
        }

        // Remove unnecessary whitespace and trailing semicolon
        $styles = preg_replace( '/\\s*{\\s*/', '{', $styles );
        $styles = preg_replace( '/;?\\s*}\\s*/', '}', $styles );
        $styles = preg_replace( '/\s+/', ' ', $styles );

        // Remove unnecessary whitespace before and after semicolons
        $styles = preg_replace( '/\\s*;\\s*/', ';', $styles );

        // Line breaks and tabs
        $styles = str_replace( [ "\r\n", "\r", "\n", "\t" ], '', $styles );

        // Single space next to commas
        $styles = str_replace( [ ', ', ' ,' ], ',', $styles );

        // Single space around combinators
        $styles = str_replace( [ ' > ', '> ', ' >' ], '>', $styles );
        $styles = str_replace( [ ' ~ ', '~ ', ' ~' ], '~', $styles );
        $styles = str_replace( [ '- ', '+ ' ], [ ' + ', ' - ' ], $styles );

        // Unnecessary unit
        $styles = str_replace( [ ' 0px', ' 0em', ' 0rem' ], ' 0', $styles );

        // var - need to if there's something before it
        $styles = str_replace( '\wvar', ' var', $styles );
        $styles = str_replace( '. var', '.var', $styles );
        $styles = str_replace( '\- var', '\-var', $styles );

        // Unnecessary leading zero
        $styles = preg_replace( '/(\b0\.\b)|(var|url|calc)\([^)]*\){}/', '.', $styles );

        return trim( $styles );
    }

    /**
     * Quick and dirty minification of JavaScript.
     *
     * TODO : [low] Further optimize
     *
     * @param string  $js
     *
     * @return string
     */
    public static function scripts( string $js ) : string {
        return Minify::trimWhitespace(
            string         : Minify::trimComments( $js, single : true ),
            removeTabs     : true,
            removeNewlines : true,
        );
    }


    /**
     * Optimize an SVG string
     *
     * - Removes all whitespace, including tabs and newlines
     * - Removes consecutive spaces
     * - Removes the XML namespace by default
     *
     * @param ?string  $string                The string SVG string
     * @param bool     $preserveXmlNamespace  Preserve the XML namespace
     * @param bool     $prettyPrint
     *
     * @return string
     */
    public static function svg(
        ?string $string,
        bool    $preserveXmlNamespace = false,
        bool    $prettyPrint = false,
    ) : string {

        // Bail early if the string is empty or null
        if ( !$string ) {
            return '';
        }

        // Remove the XML namespace if requested
        if ( !$preserveXmlNamespace ) {
            return preg_replace(
                pattern     : '#(<svg[^>]*?)\s+xmlns="[^"]*"#',
                replacement : '$1',
                subject     : $string,
            );
        }

        // Following TODOs should find a home in the SVG class, as they add to the SVG string
        // The Trim class should only be used stripping unwanted substrings
        // They have just been put here because it is convenient for me right now

        // TODO - Automatically add height and width attributes based on viewBox

        // TODO - Automatically add viewBox attribute based on width and height

        // TODO - Automatically add preserveAspectRatio attribute based on width and height

        // TODO - Warn if baked-in colors are used, preferring 'currentColor' instead

        // TODO - Option to use CSS variables

        return Str::trimWhitespace( $string, true, true );
    }


}