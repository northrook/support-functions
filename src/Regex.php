<?php

namespace Northrook\Support;

final class Regex {

    /**
     * Extracts HTML tags from a given string based on the specified tag.
     *
     * @param  string            $string      The input string to extract tags from.
     * @param  string            $tag         The HTML tag to extract.
     * @param  bool              $returnFirst Whether to return only the first occurrence of the tag. Default is false.
     * @return null|array|object Returns an array of objects representing the extracted tags. Each object has properties 'element' and 'content'.
     */
    public static function extractHtmlTags( string $string, string $tag, bool $returnFirst = false ): array | object | null {
        preg_match_all(
            "/<$tag\s*[^>]*>(.*?)<\/$tag>/",
            $string,
            $tags,
            PREG_SET_ORDER
        );

        foreach ( $tags as $key => $value ) {
            $tags[$key] = (object) array_combine(
                ['element', 'content'],
                $value
            );
        }

        if ( $returnFirst ) {
            return $tags[0] ?? null;
        }

        return $tags;
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