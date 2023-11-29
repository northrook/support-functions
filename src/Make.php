<?php

namespace Northrook\Support;

abstract class Make {

    public static function meta( string $name, ?string $content = null ): string {
        return $content ? "<meta name=\"$name\" content=\"$content\">" : '';
    }

    // Find a <H1> tag, else first paragraph
    public static function title(string $content): ?string {
        $title = 'title from content';
        return $content;
    }

    public static function keywords( string | array | null $content, string $separator = ', ', ?int $limit = null ): ?array {

        if ( $content === null ) {
            return null;
        }

        $keywords = [];

        if ( is_string( $content ) ) {
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

        return $keywords;
    }
}