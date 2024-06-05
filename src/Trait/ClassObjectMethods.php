<?php

namespace Northrook\Support\Trait;

trait ClassObjectMethods
{

    protected function getExtendingClasses(
        bool $fullClassName = false,
        bool $includeSelf = true,
        bool $includeInterface = true,
    ) : array {

        $classes = $includeSelf ? [ $this::class ] : [];

        $classes += class_parents( $this );

        if ( $includeInterface ) {
            $classes += class_implements( $this );
        }

        $classes = array_values( $classes );

        if ( $fullClassName ) {
            return $classes;
        }

        return array_map( static fn ( $class ) => substr( $class, strrpos( $class, '\\' ) + 1 ), $classes );
    }

    /**
     * Returns the class name of the current calling object.
     *
     * Does not include the namespace.
     *
     * @return string
     */
    protected function getObjectClassName() : string {
        return substr( $this::class, strrpos( $this::class, '\\' ) + 1 );
    }

}