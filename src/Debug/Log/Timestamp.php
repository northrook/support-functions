<?php
/*
 * Copyright (c) 2024. Lorem ipsum dolor sit amet, consectetur adipiscing elit.
 * Morbi non lorem porttitor neque feugiat blandit. Ut vitae ipsum eget quam lacinia accumsan.
 * Etiam sed turpis ac ipsum condimentum fringilla. Maecenas magna.
 * Proin dapibus sapien vel ante. Aliquam erat volutpat. Pellentesque sagittis ligula eget metus.
 * Vestibulum commodo. Ut rhoncus gravida arcu.
 */

namespace Northrook\Support\Debug\Log;

use DateTimeImmutable;
use DateTimeZone;
use Northrook\Support\Str;

class Timestamp
{

	private const DEFAULT_FORMAT   = 'd-m-Y H:i:s';
	private const DEFAULT_TIMEZONE = 'Europe/London';

	private readonly DateTimeImmutable $DateTime;
	private readonly DateTimeZone      $timezone;
	public readonly int                $timestamp;

	public function __construct(
		null | string | int $timestamp = null,
		DateTimeZone        $timezone = null,
	) {
		$this->timestamp = $this::getUnixTimestamp( $timestamp );

	}

	public function __toString() : string {
		return $this->format();
	}

	/**
	 * @link https://secure.php.net/manual/en/datetime.format.php
	 * @param  string  $format
	 * Format accepted by  {@link https://secure.php.net/manual/en/function.date.php date()}.
	 *
	 * @return string
	 */
	public function format( string $format = Timestamp::DEFAULT_FORMAT ) : string {
		return $this->getDateTime()->format( $format );
	}


	public function getDateTime() : DateTimeImmutable {

		if ( isset( $this->DateTime ) ) {
			return $this->DateTime;
		}

		$this->DateTime = ( new DateTimeImmutable() )
			->setTimezone( new DateTimeZone( self::DEFAULT_TIMEZONE ) )
			->setTimestamp( $this->timestamp )
		;

		return $this->DateTime;
	}

	/** Formats provided datetime string to Unix Timestamp
	 *
	 * If malformed or null string is provided; return `time()`
	 *
	 * @param  string|int|null  $time
	 *
	 * @return int Unix Timestamp
	 *
	 * @see      time(), DateTime::setTimestamp
	 */
	public static function getUnixTimestamp( string | int | null $time ) : int {
		return Str::isNumeric( $time ) ?: strtotime( $time ) ?: time();
	}

}