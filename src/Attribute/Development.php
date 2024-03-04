<?php

namespace Northrook\Support\Attribute;

use Attribute;

#[Attribute( Attribute::TARGET_FUNCTION | Attribute::TARGET_METHOD | Attribute::TARGET_PARAMETER | Attribute::TARGET_PROPERTY )]
class Development
{
	public function __construct(
		string  $note,
		?string $untilVersion = '1.0.0',
	) {}
}