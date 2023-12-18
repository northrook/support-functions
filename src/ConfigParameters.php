<?php

namespace Northrook\Support;

trait ConfigParameters {

    public static function config( ?string $key = null, mixed $default = null ): mixed {
        $config = Config::get();

        if ( ! $key ) {
            return $config;
        }

        $settings = $config->settings();

        $settings = Arr::dot( $settings );

        return $settings->get( $key, $default );
    }

}