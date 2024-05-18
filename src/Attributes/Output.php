<?php

namespace Northrook\Support\Attributes;

use Attribute;

#[Attribute( Attribute::TARGET_ALL )]
class Output
{
    /**
     * @param null|string|array{class:class-string, method: callable}  $usedBy
     */
    public function __construct(
        null | string | array $usedBy = null,
    ) {}

}