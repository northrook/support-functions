<?php

namespace Northrook\Support;

use Northrook\Support\Arr\ArrayFunctions;
use Northrook\Support\Arr\DotArray;

/**
 * TODO [low] Integrate features from https://github.com/adbario/php-dot-notation/blob/3.x/src/Dot.php
 *   ✅ Dot notation
 */
final class Arr
{
    use ArrayFunctions;

    public static function dot( mixed $items, bool $getObject = false, string $delimiter = '.' ) : DotArray {
        return new DotArray( $items, $delimiter );
    }
}