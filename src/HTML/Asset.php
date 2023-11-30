<?php

namespace Northrook\Support\HTML;

use Northrook\Support\Config;
use Northrook\Support\ConfigParameters;
use Northrook\Support\Str;

final class Asset {

    use ConfigParameters;
    private const VERSION_PREFIX = '?v=';

    public readonly ?string $path;
    public readonly string $url;
    public readonly string $type;
    public readonly string $version;
    public readonly bool $exists;

    public function __construct( string $path, ?string $type = 'auto' ) {
        $this->path    = $this->assetPath( $path );
        $this->type    = $this->assetType( $type );
        $this->version = $this->assetVersion();
        $this->url     = $this->assetUrl( true );
    }

    public function link( ?string $type = null ): string {
        $type ??= [
            'css' => 'stylesheet',
            'js'  => 'script',
        ][$this->type];

        return "<link rel=\"$type\" href=\"$this->url\">";
    }

    private function assetPath( string $path ): ?string {
        $providedPath = Str::filepath( $path, $this::config()->assetsDir );
        if ( file_exists( $providedPath ) ) {

            $this->exists = true;

            return $providedPath;
        }

        // @todo Log to debug

        $this->exists = false;

        return null;
    }

    private function assetUrl( bool $withVersion = false, bool $relative = false ): string {
        $url = str_replace( $this::config()->rootDir, '', $this->path );

        if ( $withVersion ) {
            $url = $url . self::VERSION_PREFIX . $this->version;
        }

        return Str::start( $url, '\\' );
    }

    private function assetType( ?string $type = 'auto' ): string {
        return $type === 'auto' ? pathinfo( $this->path, PATHINFO_EXTENSION ) : $type;
    }

    private function assetVersion(): string {
        $version = filemtime( $this->path );

        return $version;
    }

}