<?php

namespace Northrook\Support;

/**
 * Functions for optimizing and cleaning up strings.
 *
 * @author  Martin Nielsen <mn@northrook.com>
 */
class Trim
{
    /**
     * Regex patterns for removing comments from a string.
     *
     * - Matches from the start of the line
     * - Includes the following line break
     */
    public const COMMENTS = [
        'php'    => '#^\h*?/\*\*.*?\*/\R#ms',  // PHP block comments
        'single' => '#^\h*?//.+?\R#m',         // Single line comments
        'html'   => '#^\h*?<!--.*?-->\R#ms',   // HTML comments
        'latte'  => '#^\h*?{\*.*?\*}\R#ms',    // Latte comments
        'twig'   => '/^\h*?{#.*?#}\R/ms',      // Twig comments
        'blade'  => '#^\h*?{{--.*?--}}\R#ms',  // Blade comments
    ];


    /**
     * Remove comments from a string.
     *
     * Supports:
     * - PHP docblock comments
     * - Single line comments `// ...`
     * - HTML comments `<!-- ... -->`
     * - Latte comments `{* ... *}`
     * - Twig comments `{# ... #}`
     * - Blade comments `{{-- ... --}}`
     *
     * How to use:
     * - All comments will be trimmed by default.
     * - Pass `true` to trim specific languages.
     * - Pass `false` to preserve those, but trim others.
     *
     * @param ?string  $string  The string to trim comments from
     * @param ?bool    $php     true
     * @param ?bool    $single  true
     * @param ?bool    $html    true
     * @param ?bool    $latte   true
     * @param ?bool    $twig    true
     * @param ?bool    $blade   true
     *
     * @return string
     */
    public static function comments(
        ?string $string,
        ?bool   $php = null,
        ?bool   $single = null,
        ?bool   $html = null,
        ?bool   $latte = null,
        ?bool   $twig = null,
        ?bool   $blade = null,
    ) : string {

        // Bail early if the string is empty or null
        if ( !$string ) {
            return '';
        }

        $options = Get::booleanOptions( func_get_args() );

        $patterns = array_filter(
            array    : Trim::COMMENTS,
            callback : static fn ( $key ) => $options[ $key ] ?? false,
            mode     : ARRAY_FILTER_USE_KEY,
        );

        return preg_replace(
            pattern     : $patterns,
            replacement : '',
            subject     : $string,
        );
    }

    /**
     * Compress a string by removing consecutive whitespace and empty lines.
     *
     * - Removes empty lines
     * - Removes consecutive spaces
     * - Does not remove tabs, newlines, or carriage returns by default
     *
     * @param ?string  $string          The string to trim
     * @param bool     $removeTabs      Also remove tabs
     * @param bool     $removeNewlines  Also remove newlines
     *
     * @return string
     */
    public static function whitespace(
        ?string $string,
        bool    $removeTabs = false,
        bool    $removeNewlines = false,
        bool    $tidyHTML = true,
    ) : string {

        // Bail early if the string is empty or null
        if ( !$string ) {
            return '';
        }

        // Remove all whitespace, including tabs and newlines
        if ( $removeTabs && $removeNewlines ) {
            $string = preg_replace( '/\s+/', ' ', $string );
        }
        // Remove tabs only
        elseif ( $removeTabs ) {
            $string = str_replace( '\t', ' ', $string );
        }
        // Remove newlines only
        elseif ( $removeNewlines ) {
            $string = str_replace( "\R", ' ', $string );
        }

        $string = preg_replace(
            [
                '/^\s*?$\n/m', // Remove empty lines
                '/ +/',        // Remove consecutive spaces
            ],
            [
                '',            // Remove empty lines
                ' ',           // Remove consecutive spaces
            ],
            $string,
        );

        if ( $tidyHTML ) {
            $string = preg_replace(
                [
                    '/<\s+/', // Fix opening tags
                    '/\s+>/', // Fix closing tags
                ],
                [ '<', '>' ],
                $string,
            );
        }

        return $string;
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
     *
     * @return string
     */
    public static function svg(
        ?string $string,
        bool    $preserveXmlNamespace = false,

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

        return Trim::whitespace( $string, true, true );
    }

}