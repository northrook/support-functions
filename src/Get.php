<?php

namespace Northrook\Support;

class Get {

    use ConfigParameters;

    /**
     * @TODO [mid] Add $class support
     *
     *
     * @param  string|null   $get
     * @param  string|null   $pack
     * @param  string|null   $class
     * @param  float|null    $stroke
     * @return string|null
     */
    public static function icon(
            ?string $get,
            ?string $pack = null,
            ?string $class = null,
            ?float $stroke = null,
        bool $raw = false,
    ): ?string {
        if ( ! $get ) {
            return null;
        }

        $get  = array_filter( explode( ':', $get ) );
        $icon = [
            'name' => $get[0] ?? null,
            'pack' => $get[1] ?? $pack ?? 'lucide',
        ];

        $path = Str::filepath( Get::config()->iconsDir . '/' . $icon['pack'] . '/' . $icon['name'] . '.svg' );
        if ( ! file_exists( $path ) ) {
            return null;
        }

        $icon = file_get_contents( $path );
        $stroke ??= 1.5;
        $icon = preg_replace( '/ stroke-width=".*?"/', " stroke-width=\"$stroke\"", $icon );
        if ( $raw ) {
            return $icon;
        }

        return "<i class=\"icon\">$icon</i>";
    }
}