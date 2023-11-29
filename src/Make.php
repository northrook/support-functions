<?php

namespace Northrook\Support;

abstract class Make {

    private static ?string $_contentCache = null;

    private static function cache( ?string $content = null ): string {

        if ( Make::$_contentCache === null ) {
            $content = strip_tags(
                $content,
                ['h1', 'h2', 'h3', 'p']
            );

            Make::$_contentCache = $content;
        }

        return Make::$_contentCache;
    }

    public static function meta( string $name, ?string $content = null ): string {
        return $content ? "<meta name=\"$name\" content=\"$content\">" : '';
    }

    public static function title( string $content, ?string $tryTag = 'h1' ): ?string {

        $title   = null;
        $content = self::cache( $content );

        if ( ! $content ) {
            return null;
        }

        if ( $tryTag && str_contains(
            $content,
            "<$tryTag",
        ) ) {
            $tags  = Regex::extractHtmlTags( $content, $tryTag, true );
            $title = $tags->content;
        }

        return $title;
    }

    public static function description( string $content ): ?string {

        $description = null;
        $content     = self::cache( $content );
        $maxLengh    = 180;

        if ( ! $content ) {
            return null;
        }

        if ( str_contains(
            $content,
            "<p",
        ) ) {
            $tags = Regex::extractHtmlTags( $content, 'p', true );
            // @todo Ensure we get the correct string length, we may need to use several paragraphs
            $description = $tags->content;
        } else {
            $description = substr( strip_tags( $content ), 0, $maxLengh );
        }

        return $description;
    }

    public static function keywords( string | array | null $content, ?string $separator = null, ?int $limit = null, bool $string = false ): string | array | null {

        if ( $content === null ) {
            return null;
        }

        $keywords = [];

        if ( is_string( $content ) ) {
            $content = strip_tags( self::cache( $content ) );

            if ( ! $content ) {
                return null;
            }

            $content = str_replace( ["'", '"', '.', ',', ';', "\n", "\r"], '', $content );
            $content = explode( ' ', $content );
        }

        if ( is_array( $content ) ) {
            $content = array_filter( $content );
        }

        foreach ( $content as $key => $value ) {
            $value = mb_strtolower( $value );
            if ( in_array( $value, Stopwords::get( 'en' ) ) || in_array( $value, $keywords ) ) {
                continue;
            } else {
                $keywords[] = $value;
            }
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