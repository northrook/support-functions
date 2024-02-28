<?php

namespace Northrook\Support\Attribute;

use Attribute;

#[Attribute( Attribute::TARGET_CLASS | Attribute::TARGET_METHOD )]
class Development
{
	public function __construct(
		string $note,
		string $untilVersion = '1.0.0',
	) {}
}