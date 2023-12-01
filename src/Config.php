<?php

namespace Northrook\Support;

final class Config {

    private static Config $config;

    public readonly string $rootDir;
    public readonly string $publicDir;
    public readonly string $cacheDir;
    public readonly string $iconsDir;

    private array $settings = [
        'cache' => [
            'invalidateAll' => true,
        ],
    ];

    public function __construct(
        string $rootDir,
        string $publicDir,
            ?string $cacheDir = null,
            ?string $iconsDir = null,
            ?array $settings = null
    ) {
        $this->rootDir   = $rootDir;
        $this->publicDir = $publicDir;
        $this->cacheDir  = $cacheDir ?? $this->rootDir . 'cache';
        $this->iconsDir  = $iconsDir ?? $this->rootDir . '/assets/icons';
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