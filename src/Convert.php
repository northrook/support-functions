<?php

namespace Northrook\Support;

class Convert
{

    public static function pxToRem( int | float | null $px, int $base = 16 ) : float | null {
        return $px ? $px / $base : $px;
    }

    
}