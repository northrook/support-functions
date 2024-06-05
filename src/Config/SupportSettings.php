<?php

namespace Northrook\Support\Config;

use Northrook\Logger\Log;

trait SupportSettings
{
    private const DEFAULT = [
        'str.enforceAscii'  => false, // Will throw an exception if voku\helper\ASCII is not installed
        'str.slugSeparator' => '-',
        'str.asciiLanguage' => 'en',
    ];

    public static array $whoCalled = [];

    private static bool $defaults = true;

    protected static array $supportSettings = [];
    
    public static function setSupportSettings( array $settings, bool $overwrite = false ) : array {

        // Only set the settings if they are not already set, or if $overwrite is true
        if ( static::$defaults || $overwrite ) {
            self::$supportSettings = array_merge( self::$supportSettings, $settings );
        }
        else {
            Log::Warning(
                'Could not set {name}.They have already been set. To force this, pass {argument} as {bool}.',
                [
                    'name'            => 'SupportSettings',
                    'argument'        => 'overwrite',
                    'bool'            => 'true',
                    'passedSettings'  => $settings,
                    'currentSettings' => self::$supportSettings,
                ],
            );
        }

        // Denote that the settings are being set outside the class
        self::$defaults = false;

        // Return the current settings
        return self::$supportSettings;
    }

    protected static function getSetting( string $key ) : mixed {
        $who = static::class;
        return self::$supportSettings[ $who ][ $key ] ?? null;
    }

    private static function getSupportSettings() : array {
        return self::$supportSettings ?? self::$supportSettings = self::DEFAULT;
    }
}