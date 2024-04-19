<?php

namespace Northrook\Support\Return;

use JsonException;
use Northrook\Logger\Debug;
use Northrook\Logger\Log;
use Northrook\Logger\Log\Level;
use Throwable;

/**
 * @property bool   $success
 * @property string $message
 */
class Status
{

    private bool       $status;
    private ?string    $message = null;
    private ?Throwable $exception;
    private array      $tasks   = [];

    public readonly string $id;
    public readonly Level  $type;

    public function __construct(
        ?string $id = null,
        Level   $type = Level::INFO,
    ) {
        $this->id   = $id ?? Debug::backtrace()->getCaller();
        $this->type = $type;
    }

    public function __get( string $name ) : mixed {
        if ( property_exists( $this, $name ) ) {
            return $this->$name ?? null;
        }
        return false;
    }

    public function __set( string $name, $value ) : void {
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
            $this->type,
            'The Status {id} has reported an error.',
            [
                'id'      => $this->id,
                'message' => $this->message,
                'type'    => $this->type,

            ],
        );

        if ( $this->exception ) {
            throw $this->exception;
        }
    }

    public function hasException() : bool {
        return $this->exception !== null;
    }


    public function setException( \Exception $exception ) : Status {
        $this->exception = $exception;
        return $this;
    }
}