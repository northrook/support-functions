<?php


namespace Northrook\Support\Debug\Log;

use Psr\Log\LogLevel;

class Entry
{
	public readonly string $timestamp;

	public function __construct(
		public readonly string   $message,
		public readonly mixed    $dump,
		public readonly LogLevel $severity,
	) {
		// Provide a Timestamp Type with current time()
		$this->timestamp = date( 'Y-m-d H:i:s' );
	}
}