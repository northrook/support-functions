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
            ?string $cacheDir,
            ?array $settings = null
    ) {
        $this->settings  = $settings ?? $this->settings;
        $this->rootDir   = $rootDir;
        $this->assetsDir = $assetsDir;
        $this->cacheDir  = $cacheDir ?? $this->rootDir . '/cache';
        static::$config  = $this;
    }

    public static function get(): Config {
        return static::$config;
    }

    public function cache(): object {
        return (object) $this->settings['cache'];
    }
}