<?php

namespace Northrook\Support\HTML;

use Northrook\Support\Config;
use Northrook\Support\ConfigParameters;
use Northrook\Support\Str;

final class Asset {

    use ConfigParameters;

    public readonly string $path;
    public readonly string $type;

    public function __construct( string $path, ?string $type = 'auto'    ) {
        $this->path = Str::filepath( $path, $this::config()->assetsDir );
        $this->type = $this->assetType( $type );
    }

    private function assetType( ?string $type = 'auto' ): string {
        return $type === 'auto' ? pathinfo( $this->path, PATHINFO_EXTENSION ) : $type;
    }

}