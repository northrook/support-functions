<?php

namespace Northrook\Support;

class Trim
{
    private const COMMENTS = [
        'php'    => '#\/\*\*.*?\*\//su',
        'single' => '#\/\/.*?$/m',
        'html'   => '#<!--.*?-->/su',
        'latte'  => '#{\*.*?\*}/su',
        'twig'   => '#{#.*?#}/su',
        'blade'  => '#{{--.*?--}}/su',
    ];

    public static function comments(
        string $string,
        bool   $php = true,
        bool   $single = true,
        bool   $html = true,
        bool   $latte = true,
        bool   $twig = true,
        bool   $blade = true,
    ) : string {

        $filter = array_filter( get_defined_vars(), static fn ( $value ) => !is_bool( $value ) );

        $patterns = array_filter(
            array    : Trim::COMMENTS,
            callback : static fn ( $key ) => $filter[ $key ] ?? false,
            mode     : ARRAY_FILTER_USE_KEY,
        );

        return preg_replace(
            pattern     : $filter,
            replacement : '',
            subject     : $string,
        );
    }

    public static function left( string $string, int $length ) : string {
        return substr( $string, 0, $length );
    }

    public static function right( string $string, int $length ) : string {
        return substr( $string, -1 * $length );
    }
}