<?php

namespace Northrook\Support;

use Northrook\Support\Debug\Log\Level;
use Northrook\Support\Facades\StaticClassTrait;

final class Timer
{

	public const FORMAT_S  = 1_000_000_000;
	public const FORMAT_MS = 1_000_000;
	public const FORMAT_US = 1_000;
	public const FORMAT_NS = 1;

	use StaticClassTrait;

	private static array $events = [];

	public static function start( string $name, bool $override = false ) : void {

		if ( isset( self::$events[ $name ] ) && !$override ) {
			Debug::log(
				message  : 'Timer already started: ' . $name,
				severity : Level::WARNING,
			);
			return;
		}

		Timer::$events[ $name ] = [ 'running' => hrtime( true ) ];

	}

	public static function stop( string $name ) : ?int {

		if ( !isset( Timer::$events[ $name ] ) && Timer::$events[ $name ][ 'running' ] ) {
			Debug::log(
				message  : 'Timer not started: ' . $name,
				dump     : [ 'events' => Timer::$events ],
				severity : Level::WARNING,
			);
			return null;
		}

		$time = hrtime( true ) - Timer::$events[ $name ][ 'running' ];

		Timer::$events[ $name ] = $time;

		return $time;

	}

	public static function get(
		string     $event,
		int | bool $format = Timer::FORMAT_MS,
		bool       $stop = true,
	) : null | string | float | int {

		if ( !isset( Timer::$events[ $event ] ) ) {
			Debug::log(
				message  : 'Timer requested, but not started: ' . $event,
				severity : Level::WARNING,
			);
			return null;
		}

		$timer = Timer::$events[ $event ];

		if ( Arr::keyExists( $timer, [ 'running' ] ) ) {
			if ( $stop ) {
				$timer = Timer::stop( $event );
			}
			else {
				Debug::log(
					message  : "Event $event found, but it is currently running.",
					severity : Level::WARNING,
				);
				return null;
			}
		}

		if ( $format === false ) {
			return $timer;
		}

		return ltrim( number_format( $timer / $format, 3 ), '0' );

	}

	public static function getAll() : array {
		return Timer::$events;
	}

}