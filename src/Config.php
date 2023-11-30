<?php

namespace Northrook\Support;

final class Config {
	
    private static Config $config;

    public function __construct(
        public readonly string $rootDir,
        public readonly string $assetsDir,
    ) {
        static::$config = $this;
    }

    public static function get(): Config {
        return static::$config;
    }
}