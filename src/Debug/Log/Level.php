<?php
/*
 * Copyright (c) 2024. Lorem ipsum dolor sit amet, consectetur adipiscing elit.
 * Morbi non lorem porttitor neque feugiat blandit. Ut vitae ipsum eget quam lacinia accumsan.
 * Etiam sed turpis ac ipsum condimentum fringilla. Maecenas magna.
 * Proin dapibus sapien vel ante. Aliquam erat volutpat. Pellentesque sagittis ligula eget metus.
 * Vestibulum commodo. Ut rhoncus gravida arcu.
 */

namespace Northrook\Support\Debug\Log;

enum Level : int
{
	/**
	 * Detailed debug information
	 */
	case DEBUG = 100;

	/**
	 * Interesting events
	 *
	 * Examples: User logs in, SQL logs.
	 */
	case INFO = 200;

	/**
	 * Uncommon events
	 */
	case NOTICE = 250;

	/**
	 * Exceptional occurrences that are not errors
	 *
	 * Examples: Use of deprecated APIs, poor use of an API,
	 * undesirable things that are not necessarily wrong.
	 */
	case WARNING = 300;

	/**
	 * Runtime errors
	 */
	case ERROR = 400;

	/**
	 * Critical conditions
	 *
	 * Example: Application component unavailable, unexpected exception.
	 */
	case CRITICAL = 500;

	/**
	 * Action must be taken immediately
	 *
	 * Example: Entire website down, database unavailable, etc.
	 * This should trigger the SMS alerts and wake you up.
	 */
	case ALERT = 550;

	/**
	 * Urgent alert.
	 */
	case EMERGENCY = 600;

	public function name() : string {
		return self::NAMES[ $this->value ];
	}

	public const NAMES = [
		100 => 'Debug',
		200 => 'Info',
		250 => 'Notice',
		300 => 'Warning',
		400 => 'Error',
		500 => 'Critical',
		550 => 'Alert',
		600 => 'Emergency',
	];
}