<?php

namespace Northrook\Support;

use Northrook\src\Internal\ExtractHtmlAttributesTrait;
use Northrook\src\Internal\PrettyHtmlMarkup;

class Html
{
    use ExtractHtmlAttributesTrait;

    public static function pretty( string $html ) : PrettyHtmlMarkup {
        return new PrettyHtmlMarkup( $html );
    }
}