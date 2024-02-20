<?php


namespace Northrook\Support\Debug\Log;


class Entry
{
//	public readonly Timestamp $timestamp;

	public function __construct(
		public readonly string    $message,
		public readonly mixed     $dump,
		public readonly Level     $level = Level::Debug,
		public readonly Timestamp $timestamp = new Timestamp(),
	) {
//		$this->timestamp = new Timestamp();
	}
}