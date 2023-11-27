<?php

namespace Northrook\Support;

abstract class Make {

    public static function meta( string $name, ?string $content = null ) : string {
        return $content ? "<meta name=\"$name\" content=\"$content\">" : '';
    }

    public static function keywords( string | array | null $content, string $separator = ', '  ) : ?string {

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
            if ( in_array( $value, Stopwords::get('en') ) || in_array( $value, $keywords ) ) {
                continue;
            } else {
                $keywords[] = $value;
            }
        }
        
        return implode( ', ', $keywords );
    }
}