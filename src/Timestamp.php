<?php

namespace Northrook\Support;

use Northrook\Core\Trait\PropertyAccessor;

/**
 * @property-read int $timestamp
 *
 * @author  Martin Nielsen <mn@northrook.com>
 */
final class Timestamp
{
    use PropertyAccessor;

    public const FORMAT_HUMAN            = 'd-m-Y H:i:s';
    public const FORMAT_W3C              = 'Y-m-d\TH:i:sP';
    public const FORMAT_RFC3339          = 'Y-m-d\TH:i:sP';
    public const FORMAT_RFC3339_EXTENDED = 'Y-m-d\TH:i:s.vP';

    public readonly \DateTimeZone      $timezone;
    public readonly \DateTimeImmutable $datetime;

    public function __construct(
        string $timezone = 'UTC',
    ) {
        try {
            $this->timezone = new \DateTimeZone( $timezone );
        }
        catch ( \Exception $exception ) {
            throw new \InvalidArgumentException(
                message  : "Unable to create a new DateTimeZone object for $timezone.",
                code     : 500,
                previous : $exception,
            );
        }

        try {
            $this->datetime = new \DateTimeImmutable( 'now', $this->timezone );
        }
        catch ( \Exception $exception ) {
            throw new \InvalidArgumentException(
                message  : "Unable to create a new DateTimeImmutable object for $timezone.",
                code     : 500,
                previous : $exception,
            );
        }
    }

    public function format( string $format = self::FORMAT_HUMAN, bool $includeTimezone = true ) : string {

        $time = $this->datetime->format( $format );

        return $includeTimezone ? $time . ' ' . $this->timezone->getName() : $time;

    }

    public function __get( string $property ) {
        return match ( $property ) {
            'timestamp' => $this->datetime->getTimestamp(),
        };
    }
}