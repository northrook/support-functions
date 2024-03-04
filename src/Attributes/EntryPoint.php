<?php

namespace Northrook\Support\Attributes;

use Attribute;
use JetBrains\PhpStorm\ExpectedValues;

#[Attribute( Attribute::TARGET_ALL )]
class EntryPoint
{

	public function __construct(
		#[ExpectedValues( [ 'config/service.php', 'autowire', 'new', 'static' ] )]
		?string $via = null,
		?string $usedBy = null,
	) {}

}