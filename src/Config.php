<?php

namespace Northrook\Support;

final class Config {

    private static Config $config;

    public readonly string $rootDir;
    public readonly string $assetsDir;
    public readonly string $cacheDir;

    private array $settings = [
        'cache' => [
            'invalidateAll' => true,
        ],
    ];

    public function __construct(
        string $rootDir,
        string $assetsDir,
            ?string $cacheDir = null,
            ?array $settings = null
    ) {
        $this->rootDir   = $rootDir;
        $this->assetsDir = $assetsDir;
        $this->cacheDir  = $cacheDir ?? $this->rootDir . 'cache';
        $this->settings  = $settings ?? $this->settings;
        static::$config  = $this;
    }

    public static function get(): Config {
        return static::$config;
    }

    public function cache(): object {
        return (object) $this->settings['cache'];
    }
}