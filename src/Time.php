<?php

declare( strict_types = 1 );

namespace Northrook\Support;

class Time implements \Stringable
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

    public readonly int    $timestamp;
    public readonly string $datetime;
    public readonly string $timezone;

    public function __construct(
        string | \DateTimeInterface $dateTime = 'now',
        string | \DateTimeZone      $timezone = 'UTC',
        string                      $format = 'Y-m-d H:i:s',
    ) {
        $this->setDateTime( $dateTime, $timezone );

        $this->timestamp = $this->dateTimeImmutable->getTimestamp();
        $this->timezone  = $this->dateTimeImmutable->getTimezone()->getName();
        $this->datetime  = $this->dateTimeImmutable->format( $format ) . ' ' . $this->timezone;
    }

    final public function format( string $format ) : string {
        return $this->dateTimeImmutable->format( $format );
    }

    private function setDateTime(
        string | \DateTimeInterface $dateTime = 'now',
        string | \DateTimeZone      $timezone = 'UTC',
    ) : void {
        try {
            $this->dateTimeImmutable = new \DateTimeImmutable( $dateTime, timezone_open( $timezone ) ?: null );
        }
        catch ( \Exception $exception ) {
            throw new \InvalidArgumentException(
                message  : "Unable to create a new DateTimeImmutable object for $timezone.",
                code     : 500,
                previous : $exception,
            );
        }
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

            $time = (float) number_format( $time / $format, strlen( (string) $time ) );


            // If we have leading zeros
            if ( $time < 1 ) {
                $floating = substr( (string) $time, 2 );
                $decimals += strlen( $floating ) - strlen( ltrim( $floating, '0' ) );
            }
            $time = Num::decimals( $time, $decimals );
            // dump( $decimals );

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

    public function __toString() {
        return $this->datetime;
    }
}