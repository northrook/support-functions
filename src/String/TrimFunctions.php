<?php

namespace Northrook\Support\String;

use Northrook\Support\Arr;

/**
 * Functions for optimizing and cleaning up strings.
 *
 * @author  Martin Nielsen <mn@northrook.com>
 */
trait TrimFunctions
{
    /**
     * Regex patterns for removing comments from a string.
     *
     * - Matches from the start of the line
     * - Includes the following line break
     */
    public const REGEX_COMMENT_PATTERN = [
        'php'    => '#^\h*?/\*\*.*?\*/\R#ms',  // PHP block comments
        'single' => '#^\h*?//.+?\R#m',         // Single line comments
        'block'  => '#^\h*?/\*.*?\*/\R#ms',    // Block comments
        'html'   => '#^\h*?<!--.*?-->\R#ms',   // HTML comments
        'latte'  => '#^\h*?{\*.*?\*}\R#ms',    // Latte comments
        'twig'   => '/^\h*?{#.*?#}\R/ms',      // Twig comments
        'blade'  => '#^\h*?{{--.*?--}}\R#ms',  // Blade comments
    ];

    /**
     * Remove all comments and unnecessary whitespace from a string.
     *
     * @param string  $string
     *
     * @return string
     */
    public static function squish( string $string ) : string {
        return static::trimWhitespace( static::trimComments( $string ), true, true );
    }

    /**
     * Remove comments from a string.
     *
     * Supports:
     * - PHP docblock comments
     * - Single line comments `// ...`
     * - Block comments
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
     * @param ?bool    $block   true
     * @param ?bool    $html    true
     * @param ?bool    $latte   true
     * @param ?bool    $twig    true
     * @param ?bool    $blade   true
     *
     * @return string
     */
    public static function trimComments(
        ?string $string,
        ?bool   $php = null,
        ?bool   $single = null,
        ?bool   $block = null,
        ?bool   $html = null,
        ?bool   $latte = null,
        ?bool   $twig = null,
        ?bool   $blade = null,
    ) : string {

        // Bail early if the string is empty or null
        if ( !$string ) {
            return '';
        }

        // Resolve all options
        $options = Arr::booleanValues( get_defined_vars() );

        // Get the desired patterns
        $patterns = array_filter(
            array    : static::REGEX_COMMENT_PATTERN,
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
     * - Remove tabs, newlines, and carriage returns by default
     *
     * @param ?string  $string          The string to trim
     * @param bool     $removeTabs      Also remove tabs
     * @param bool     $removeNewlines  Also remove newlines
     *
     * @return string
     */
    public static function trimWhitespace(
        ?string $string,
        bool    $removeTabs = true,
        bool    $removeNewlines = true,
    ) : string {

        // Bail early if the string is empty or null
        if ( !$string ) {
            return '';
        }

        // Trim according to arguments
        $string = match ( true ) {
            // Remove all whitespace, including tabs and newlines
            $removeTabs && $removeNewlines => preg_replace( '/\s+/', ' ', $string ),
            // Remove tabs only
            $removeTabs                    => str_replace( '\t', ' ', $string ),
            // Remove newlines only
            $removeNewlines                => str_replace( '\R', ' ', $string ),
            // Remove consecutive whitespaces
            default                        => preg_replace( '# +#', ' ', $string ),
        };

        // Remove empty lines
        $string = preg_replace( '#^\s*?$\n#m', '', $string );

        // Trim unnecessary whitespace around brackets
        $string = preg_replace( '#\s*([<>])\s*#m', '$1', $string );

        return trim( $string );
    }
}