<?php

namespace Northrook\Support\Return;

use Exception;
use JetBrains\PhpStorm\Deprecated;
use JsonException;
use Northrook\Core\Debug\Backtrace;
use Northrook\Logger\Log;
use Northrook\Logger\Log\Level;
use Throwable;

/**
 * @property bool      $set
 * @property-read      $status
 * @property-read bool $success
 * @property-read bool $failure
 * @property string    $message
 * @property Level     $level
 */
#[Deprecated( 'Use Core instead', \Northrook\Core\Service\Status::class )]
class Status
{

    private bool       $status;
    private Level      $level;
    private ?string    $message    = null;
    private ?Throwable $exception;
    private array      $tasks      = [];
    private array      $properties = [];

    public readonly string $id;

    public function __construct(
        ?string $id = null,
        Level   $type = Level::INFO,
    ) {
        $this->id    = $id ?? Backtrace::get()->caller;
        $this->level = $type;
    }

    public function __get( string $name ) : mixed {

        if ( $name === 'success' ) {
            return $this->status;
        }

        if ( $name === 'failure' ) {
            return !$this->status;
        }

        if ( property_exists( $this, $name ) ) {
            return $this->$name ?? null;
        }
        return false;
    }

    public function __set( string $name, $value ) : void {
        if ( $name === 'set' ) {
            $this->status = $value;
            return;
        }
        if ( property_exists( $this, $name ) ) {
            $this->$name = $value;
        }
        else {
            Log::Error(
                'Attempting to set unknown property {name}.',
                [
                    'name'  => $name,
                    'value' => $value,
                ],
            );
        }
    }

    public function __isset( string $name ) : bool {
        return isset( $this->$name );
    }

    public function addTask( string $label, bool | string $status ) : Status {
        try {
            $status = json_encode( $status, JSON_THROW_ON_ERROR );
        }
        catch ( JsonException $e ) {
            Log::error( $e->getMessage() );
        }
        $this->tasks[ $label ] = $status;

        return $this;
    }

    public function getTasks() : array {
        return $this->tasks;
    }

    /**
     * @throws Throwable
     */
    public function onFailure() : void {
        if ( $this->success ) {
            return;
        }

        Log::entry(
            $this->level,
            'The Status {id} has reported an error.',
            [
                'id'      => $this->id,
                'message' => $this->message,
                'type'    => $this->level,

            ],
        );

        if ( $this->exception ) {
            throw $this->exception;
        }
    }

    public function hasException() : bool {
        return $this->exception !== null;
    }


    public function setException( Exception $exception ) : Status {
        $this->exception = $exception;
        return $this;
    }
}