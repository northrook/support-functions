<?php


namespace Northrook\Support\Debug\Log;


class Entry
{

	public readonly Timestamp $Timestamp;
	public Level              $Level;

	public function __construct(
		public readonly string $message,
		public readonly mixed  $dump,
		?Level                 $level = null,
		?Timestamp             $timestamp = null,
	) {

		$this->Timestamp = $timestamp ?? new Timestamp();
		$this->Level = $level ?? Level::DEBUG;
	}
}