<?php

namespace Northrook\Support\HTML;

/// Ideally have an array handling the order, like wp_hooks

use RuntimeException;

final class Document {
	
	private static array	$blocks = [];
	private readonly string	$assetsDir;
	
	public function __construct(
		string $assetsDir = 'assets',
	) {
		$this->assignDirectory( $assetsDir );
	}
	
	public static function meta( string $name, ?string $content = null ) : string {
		return $content ? "<meta name=\"$name\" content=\"$content\">" : '';
	}
	
	
	private function assignDirectory( string $dir ) : void {
		if ( is_dir( $dir ) ) {
			$this->assetsDir = $dir;
		}
		else {
			throw new RuntimeException( "Assets directory not found: $dir" );
		}
	}

    public static function keywords( string | array | null $keywords, string $separator = ', '  ) : ?string {

        if ( $keywords === null ) {
            return null;
        }

        if ( is_string( $keywords ) ) {
            $keywords = mb_strtolower($keywords);
            $keywords = str_replace([' ', ','], ' ', $keywords);
            $keywords = array_filter(explode( ' ', $keywords ));
        }
        
        if ( is_array( $keywords ) ) {
            $keywords = implode( $separator, $keywords );
        }
    }
	
	
}