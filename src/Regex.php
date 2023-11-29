<?php

namespace Northrook\Support;

final class Regex {

    public static function extractHtmlTag( string $string, string $tag, bool $returnFirst = false ): ?array {
        preg_match( "/<$tag\s*[^>]*>(.*?)<\/$tag>/is", $string, $matches );

        if ( $returnFirst ) {
            return $matches[1] ?? null;
        }

        return $matches;
    }

    /**
     *
     * @uses preg_match_all() $match, $string, PREG_SET_ORDER
     * @param  string      $pattern
     * @param  string|null $string
     * @param  string|null $flag       s | Regex match flags
     * @param  string|bool $trim       TODO Idea is to pass characters to stop from each matched string
     * @return object
     */
    public static function matchNamedGroups(
        string $pattern,
            ?string $string,
        string | bool $trim = false
    ): object {
        $tag     = [];
        $matches = preg_match_all(
            $pattern,
            $string,
            $captured,
            PREG_SET_ORDER
        );
        if ( ! $matches ) {
            return Arr::asObject( $tag );
        }

        foreach ( $captured as $matched ) {
            $element = ['matched' => $matched[0]];
            $element += array_filter(
                $matched,
                static fn( $k ) => is_string( $k ),
                ARRAY_FILTER_USE_KEY
            );

            if ( $trim ) {
                $element = array_map(
                    static fn( $value ) => trim(
                        $value, ( $trim === true ) ? " \t\n\r\0\x0B" : $trim
                    ),
                    $element );
            }
            $tag[] = $element;
        }

        return Arr::asObject( $tag );
    }

}