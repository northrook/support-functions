<?php

namespace Northrook\Support;

/* ---

Must be able to use a provided DateTime object

Must provide public readonly:
 - timestamp : int
 - datetime  : string ( human readable )

--- */

use Northrook\Logger\Log;

class Time
{

    public const FORMAT_S                = 1_000_000_000;
    public const FORMAT_MS               = 1_000_000;
    public const FORMAT_US               = 1_000;
    public const FORMAT_NS               = 1;
    public const FORMAT_HUMAN            = 'd-m-Y H:i:s';
    public const FORMAT_W3C              = 'Y-m-d\TH:i:sP';
    public const FORMAT_RFC3339          = 'Y-m-d\TH:i:sP';
    public const FORMAT_RFC3339_EXTENDED = 'Y-m-d\TH:i:s.vP';

    private readonly \DateTimeImmutable $dateTimeImmutable;

    public function __construct(
        string | \DateTimeInterface $dateTime = 'now',
        string | \DateTimeZone      $timezone = 'UTC',
        string                      $format = 'Y-m-d H:i:s',
    ) {
        $this->setDateTime( $dateTime, $timezone );
    }


    private function setDateTime(
        string | \DateTimeInterface $dateTime = 'now',
        string | \DateTimeZone      $timezone = 'UTC',
    ) : void {
        try {
            $this->dateTimeImmutable = new \DateTimeImmutable( 'now', $this->timezone( $timezone ) );
        }
        catch ( \Exception $exception ) {
            throw new \InvalidArgumentException(
                message  : "Unable to create a new DateTimeImmutable object for $timezone.",
                code     : 500,
                previous : $exception,
            );
        }
    }


    /**
     * Converts the provided timezone to a {@see \DateTimeZone} object.
     *
     * - Will fall back to UTC if an invalid timezone is provided.
     * - Timezone objects will be returned as-is.
     * - Generated {@see \DateTimeZone} objects will be `memoized`.
     *
     * @param string|\DateTimeZone  $timezone
     *
     * @return \DateTimeZone
     */
    private function timezone( string | \DateTimeZone $timezone ) : \DateTimeZone {

        if ( $timezone instanceof \DateTimeZone ) {
            return $timezone;
        }

        $DateTimeZone = static function ( $timezone ) {
            try {
                return new \DateTimeZone( $timezone );
            }
            catch ( \Exception ) {
                Log::Error( "Unable to create DateTimeZone object for $timezone. Using UTC instead." );
                return new \DateTimeZone( 'UTC' );
            }
        };

        return Cached( $DateTimeZone, [ $timezone ] );
    }

    public static function stopwatch(
        ?int    $since = null,
        ?string $format = Time::FORMAT_MS,
        int     $decimals = 2,
        bool    $appendFormat = true,
    ) : string | float {

        if ( $since ) {
            $time = hrtime( true ) - $since;

            if ( !$format ) {
                return $time;
            }

            $time = number_format( $time / $format, strlen( $time ) );


            // If we have leading zeros
            if ( $time < 1 ) {
                $floating = substr( $time, 2 );
                $decimals += strlen( $floating ) - strlen( ltrim( $floating, '0' ) );
                $time     = Num::decimals( $time, $decimals );
            }

            if ( !$appendFormat ) {
                return $time;
            }

            return $time . [
                               Time::FORMAT_S  => 's',
                               Time::FORMAT_MS => 'ms',
                               Time::FORMAT_US => 'us',
                               Time::FORMAT_NS => 'ns',
                           ][ $format ];
        }

        return hrtime( true );
    }

}