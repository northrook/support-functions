<?php

namespace Northrook\Support;

final class Filter
{
    private function __construct() {}

    public static function url( string $string) :string {
		return preg_match('~^(?:(?:https?|ftp)://[^@]+(?:/.*)?|(?:mailto|tel|sms):.+|[/?#].*|[^:]+)$~Di', $string) ? $string : '';
    }

    /**
     * Escapes string for use everywhere inside HTML (except for comments).
     */
    public static function html( string $string ) : string {
        return htmlspecialchars( $string, ENT_QUOTES | ENT_HTML5 | ENT_SUBSTITUTE, 'UTF-8' );
    }

    public static function string( string $string ) : string {
        return htmlspecialchars( $string, ENT_NOQUOTES | ENT_SUBSTITUTE, 'UTF-8' );
    }
}