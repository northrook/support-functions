<?php

namespace Northrook\Support;

trait ConfigParameters {

    public static function config( ?string $key = null): Config {
        $config = Config::get();

        if ( ! $key ) {
            return $config;
        }

        return $config->$key;
    }

}