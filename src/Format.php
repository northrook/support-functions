<?php

namespace Northrook\Support;

class Format {


    /**
     * * Spacing, XX XX XX XX, XXXX XXX XXX etc
     * * Areacode detector, 00XX, +XX etc
     * 
     * @param string $number 
     * @return string 
     */
    public static function telephone( string $number ): string {
        return preg_replace( '/[^0-9]/', '', $number );
    }
}