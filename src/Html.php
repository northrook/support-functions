<?php

namespace Northrook\Support;

use Northrook\Support\HTML\ExtractHtmlAttributesTrait;
use Northrook\Support\HTML\PrettyHtmlMarkup;

class Html
{
    use ExtractHtmlAttributesTrait;

    public static function pretty( string $html ) : PrettyHtmlMarkup {
        return new PrettyHtmlMarkup( $html );
    }
}