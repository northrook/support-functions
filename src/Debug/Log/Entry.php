<?php


namespace Northrook\Support\Debug\Log;


class Entry
{

	public readonly Timestamp $timestamp;
	public Level              $level;

	public function __construct(
		public readonly string $message,
		public readonly mixed  $dump,
		?Level                 $level = null,
		?Timestamp             $timestamp = null,
	) {

		$this->timestamp = $timestamp ?? new Timestamp();
		$this->level = $level ?? Level::DEBUG;
	}
}