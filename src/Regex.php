<?php

namespace Northrook\Support;

final class Regex
{

    /**
     * Extracts HTML tags from a given string based on the specified tag.
     *
     * @param string  $string       The input string to extract tags from.
     * @param string  $tag          The HTML tag to extract.
     * @param bool    $returnFirst  Whether to return only the first occurrence of the tag. Default is false.
     *
     * @return null|array|object Returns an array of objects representing the extracted tags. Each object has properties 'element' and 'content'.
     */
    public static function extractHtmlTags( string $string, string $tag, bool $returnFirst = false,
    ) : array | object | null {
        
        preg_match_all(
            pattern : "#<$tag\s*[^>]*>(.*?)</$tag>#",
            subject : $string,
            matches : $tags,
            flags   : PREG_SET_ORDER,
        );

        foreach ( $tags as $key => $value ) {
            $tags[ $key ] = (object) array_combine(
                [ 'element', 'content' ],
                $value,
            );
        }

        if ( $returnFirst ) {
            return $tags[ 0 ] ?? null;
        }

        return $tags;
    }

    /**
     *
     * @param string        $pattern
     * @param string        $subject
     * @param string|bool   $trim             TODO Idea is to pass characters to stop from each matched string
     * @param string        $matchedProperty  Property to return the matched string
     * @param Return\Regex  $return
     *
     * @return object
     * @uses preg_match_all() $match, $string, PREG_SET_ORDER
     */
    public static function matchNamedGroups(
        string        $pattern,
        string        $subject,
        string | bool $trim = false,
        string        $matchedProperty = 'matched',
        Return\Regex  $return = Return\Regex::ARRAY,
    ) : object {

        $array = [];

        $count = preg_match_all(
                    $pattern,
                    $subject,
                    $matches,
            flags : PREG_SET_ORDER,
        );

        if ( !$count ) {
            return Arr::asObject( $array );
        }

        foreach ( $matches as $matched ) {
            $element = [ $matchedProperty => $matched[ 0 ] ];
            $element += array_filter(
                array    : $matched,
                callback : static fn ( $k ) : bool => is_string( value : $k ),
                mode     : ARRAY_FILTER_USE_KEY,
            );

            if ( $trim !== false ) {
                $characters = ( $trim === true ) ? " \t\n\r\0\x0B" : $trim;
                $element    = array_map(
                    callback : static fn ( $string ) : string => trim(
                        $string,
                        $characters,
                    ),
                    array    : $element,
                );
            }

            $array[] = (object) $element;
        }

        return Arr::asObject( $array );
    }

}