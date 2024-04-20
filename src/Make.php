<?php

namespace Northrook\Support;

use Northrook\Support\Config\Stopwords;

abstract class Make
{

    use ConfigParameters;

    private static ?string $_contentCache = null;

    protected static function cache( ?string $content = null ) : ?string {

        if ( $content && Make::$_contentCache === null ) {
            $content = strip_tags(
                $content,
                [ 'h1', 'h2', 'h3', 'p' ],
            );

            Make::$_contentCache = $content;
        }

        return Make::$_contentCache;
    }

    /**
     * Generate a title from the content
     *
     * @param  ?string     $content    The content to generate a title from, will be stripped of unwanted HTML, and cached
     * @param null|string  $length     Pass a number to limit the title length, or as min:max to limit the length to a range
     * @param null|string  $preferTag  Pass a tag name to prefer that tag for the title
     *
     * @return null|string
     */
    public static function title( ?string $content, ?string $length = null, ?string $preferTag = 'h1' ) : ?string {

        $title   = null;
        $content = Make::cache( $content );

        if ( !$content ) {
            return null;
        }

        if ( $preferTag && str_contains(
                $content,
                "<$preferTag",
            ) ) {
            $tags  = Regex::extractHtmlTags( $content, $preferTag, true );
            $title = $tags->content;
        }

        return $title;
    }

    public static function description( ?string $content ) : ?string {

        $description = null;
        $content     = Make::cache( $content );
        $maxLengh    = 180;

        if ( !$content ) {
            return null;
        }

        if ( str_contains(
            $content,
            "<p",
        ) ) {
            $tags = Regex::extractHtmlTags( $content, 'p', true );
            // @todo Ensure we get the correct string length, we may need to use several paragraphs
            $description = $tags->content;
        }
        else {
            $description = substr( strip_tags( $content ), 0, $maxLengh );
        }

        return $description;
    }

    public static function keywords(
        string | array | null $content, ?string $separator = null, ?int $limit = null, bool $string = false,
    ) : string | array | null {

        if ( $content === null ) {
            return null;
        }

        $keywords = [];

        if ( is_string( $content ) ) {
            $content = strip_tags( Make::cache( $content ) );

            if ( !$content ) {
                return null;
            }

            $content = str_replace( [ "'", '"', '.', ',', ';', "\n", "\r" ], '', $content );
            $content = explode( ' ', $content );
        }

        if ( is_array( $content ) ) {
            $content = array_filter( $content );
        }

        foreach ( $content as $key => $value ) {
            $value = mb_strtolower( $value );
            if ( in_array( $value, $keywords, true ) || in_array( $value, Stopwords::get(), true ) ) {
                continue;
            }

            $keywords[] = $value;
        }

        if ( $limit !== null && $limit > 0 ) {
            $keywords = array_slice( $keywords, 0, $limit );
        }

        if ( $string ) {
            return implode( $separator, $keywords );
        }

        return $keywords;
    }
}