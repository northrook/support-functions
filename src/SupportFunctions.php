<?php

namespace Northrook\Support;

trait  SupportFunctions {
	
	public static function filepath( string $path, ?string $fullPath = null ) : string {
		$path = str_replace( [ '/', '\\' ], DIRECTORY_SEPARATOR, $path );
		return str_replace( '\\\\', '\\', $fullPath ? Str::start( string : $fullPath, with : $path ) : $path );
	}
}