<?php

namespace Northrook\Support;

use JetBrains\PhpStorm\Deprecated;
use Northrook\Support\Internal\Stopwords;

class Get extends Make
{

    /**
     * Get a boolean option from an array of options.
     *
     * ⚠️ Be careful if passing other nullable values, as they will be converted to booleans.
     *
     * - Pass an array of options, `get_defined_vars()` is recommended.
     * - All 'nullable' values will be converted to booleans.
     * - `true` options set all others to false.
     * - `false` options set all others to true.
     * - Use the `$default` parameter to set value for all if none are set.
     *
     * @param array  $options  Array of options, `get_defined_vars()` is recommended
     * @param bool   $default  Default value for all options
     *
     * @return array<string, bool>
     */
    #[Deprecated]
    public static function booleanOptions( array $options, bool $default = true ) : array {

        trigger_deprecation(
            'northrook/support',
            'dev-main',
            'Use Northrook/Core/booleanOptions() instead',
        );

        // Isolate the options
        $options = array_filter( $options, static fn ( $value ) => is_bool( $value ) || is_null( $value ) );

        // If any option is true, set all others to false
        if ( in_array( true, $options, true ) ) {
            return array_map( static fn ( $option ) => $option === true, $options );
        }

        // If any option is false, set all others to true
        if ( in_array( false, $options, true ) ) {
            return array_map( static fn ( $option ) => $option !== false, $options );
        }

        // If none are true or false, set all to the default
        return array_map( static fn ( $option ) => $default, $options );
    }

    public static function stopwords( string $group = 'en' ) : array {
        trigger_deprecation(
            'northrook/support',
            'dev-main',
            'Use "northrook/content-formatter" instead',
        );
        return Stopwords::get( $group );
    }

}