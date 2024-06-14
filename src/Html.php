<?php

namespace Northrook\Support;

use Northrook\Support\Internal\ExtractHtmlAttributesTrait;
use Northrook\Support\Internal\PrettyHtmlMarkup;

class Html
{
    use ExtractHtmlAttributesTrait;

    public static function pretty( string $html ) : PrettyHtmlMarkup {
        return new PrettyHtmlMarkup( $html );
    }
}