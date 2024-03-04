<?php

namespace Northrook\Support\Attribute;

use Attribute;
use JetBrains\PhpStorm\ExpectedValues;

#[Attribute( Attribute::TARGET_FUNCTION | Attribute::TARGET_METHOD | Attribute::TARGET_PARAMETER | Attribute::TARGET_PROPERTY )]
class Development
{
	public function __construct(
		#[ExpectedValues( [
			'pending', // Not started, but planned
			'bug',     // Something is wrong, see note for details
			'static',  // "It needs improvement, but is working well, and I'll maybe get back to it"
			'MVP',     // All features are working, needs refactoring/review
			'beta',    // Fully working, but not ready for production
			'Done',    // Ready for production
		] )]
		string  $status,
		?string $note = null,
		?string $untilVersion = null,
	) {}
}