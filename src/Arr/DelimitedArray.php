<?php

namespace Northrook\src\Arr;

final class DelimitedArray
{

    /**
     * Create a new Dot instance
     *
     * @param mixed             $items
     * @param bool              $parse
     * @param non-empty-string  $delimiter  [.] The character to use as a delimiter
     *
     * @return void
     */
    public function __construct(
        array                     $items = [],
        bool                      $parse = false,
        protected readonly string $delimiter = ".",
    ) {
        $items = $this->getArrayItems( $items );

        if ( $parse ) {
            $this->set( $items );
        }
        else {
            $this->items = $items;
        }
    }
}