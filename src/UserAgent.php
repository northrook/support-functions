<?php

namespace Northrook\Support;

use foroco\BrowserDetection;

if ( ! defined( 'USER_AGENT' ) ) {
    define(
        'USER_AGENT',
        $_SERVER['HTTP_USER_AGENT'] ?? null
    );
}

/**
 * Northrook UserAgent
 *
 * @uses    BrowserDetection by Foroco for browser detection
 * @link    https://github.com/foroco/php-browser-detection
 */
final class UserAgent {

    private static BrowserDetection $browserDetection;

    private static array $getAll;
    private static array $os;
    private static array $browser;

    /**
     * Get the browser detection class
     *
     * @return BrowserDetection
     */
    public static function detect(): BrowserDetection {
        return UserAgent::$browserDetection ??= new BrowserDetection();
    }

    /**
     * Get the OS family of the current user agent
     *
     *  Pass a string to $match the current OS family.
     *  If no $match is passed, an object containing all the OS families is returned
     *   * Each key is the OS family, and the value is true if the OS family matches
     *
     *
     * @param  string|null         $match Return true if the OS family matches
     * @param  bool                $raw   Return the raw OS family array
     * @return bool|object|array
     */
    public static function OS( ?string $match = null, bool $raw = false ): bool | object | array {

        $os = UserAgent::getOS();

        if ( $raw ) {
            return $os;
        }

        $osFamily = [
            'apple'   => $os['os_family'] === 'macintosh',
            'linux'   => $os['os_family'] === 'linux',
            'windows' => $os['os_family'] === 'windows',
            'android' => $os['os_family'] === 'android',
        ];

        if ( ! $match ) {
            return (object) $osFamily;
        }

        return $osFamily[$match] ?? false;
    }

    /**
     * Returns a cached array of the getAll() function
     *
     * @return array
     */
    public static function getAll(): array {
        return UserAgent::$getAll ??= UserAgent::detect()->getAll( $_SERVER['HTTP_USER_AGENT'] );
    }

    /**
     * Returns a cached array of the getOS() function
     *
     * @return array
     */
    public static function getOS(): array {
        return UserAgent::$os ??= UserAgent::detect()->getOS( $_SERVER['HTTP_USER_AGENT'] );
    }

    /**
     * Returns a cached array of the getBrowser() function
     *
     * @return array
     */
    public static function getBrowser(): array {
        return UserAgent::$browser ??= UserAgent::detect()->getBrowser( $_SERVER['HTTP_USER_AGENT'] );
    }
}