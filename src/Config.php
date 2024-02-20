<?php

namespace Northrook\Support;

use Northrook\Support\Config\Security;
use Northrook\Support\Config\Security\Scheme;

final class Config
{
	private static Config $static;

	public readonly Security $security;


	public readonly string $baseUrl;
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
		string    $rootDir,
		string    $publicDir,
		?string   $cacheDir = null,
		?string   $iconsDir = null,
		?array    $settings = null,
		?string   $baseUrl = null,
		?Security $security = null,
	) {
		$this->rootDir = $rootDir;
		$this->publicDir = $publicDir;
		$this->cacheDir = $cacheDir ?? $this->rootDir . 'cache';
		$this->iconsDir = $iconsDir ?? $this->rootDir . 'assets' . DIRECTORY_SEPARATOR . 'icons';
		$this->settings = $settings ?? $this->settings;

		$this->baseUrl = $baseUrl ?? '/';

		$this->security = $security ?? new Security(
			Scheme::HTTPS,
		);
		Config::$static = $this;
	}

	public static function get() : Config {
		return Config::$static;
	}

	public static function security() : Security {
		return self::get()->security;
	}

	public function settings() : array {
		return $this->settings;
	}

}