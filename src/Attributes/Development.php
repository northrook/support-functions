<?php

namespace Northrook\Support\Attributes;

use Attribute;
use JetBrains\PhpStorm\ExpectedValues;

#[Attribute( Attribute::TARGET_ALL )]
class Development
{
	public function __construct(
		#[ExpectedValues( [
			'pending', // Not started, but planned
			'bug',     // Something is wrong, see note for details
			'started', // In progress
			'static',  // "It needs improvement, but is working well, and I'll maybe get back to it"
			'mvp',     // All features are working, needs refactoring/review
			'beta',    // Fully working, but not ready for production
			'done',    // Ready for production
		] )]
		string  $status,
		?string $note = null,
		?string $untilVersion = null,
	) {}
}