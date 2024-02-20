<?php

namespace Northrook\Support\Config;

use Northrook\Support\Config\Security\Scheme;

class Security
{
	public readonly string $scheme;

	public function __construct(
		Scheme $scheme = Scheme::HTTPS,
	) {
		$this->scheme = $scheme->value;
	}
}