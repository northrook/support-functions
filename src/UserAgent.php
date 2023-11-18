<?php

namespace Northrook\Support;

use foroco\BrowserDetection;

final class Agent {
	
	private static string			$userAgent;
	private static BrowserDetection	$browser;
	
	private static array	$os;
	private static object	$osFamily;
	
	public static function browser() : BrowserDetection {
		if ( ! isset( Agent::$userAgent ) ) Agent::$userAgent = $_SERVER[ 'HTTP_USER_AGENT' ];
		return Agent::$browser ??= new BrowserDetection();
	}
	
	public static function OS( bool $raw = false ) : array | object {
		$os = Agent::getOS();
		if ( $raw ) return $os;
		return Agent::$osFamily ??= (object) [
			'apple'		=> $os[ 'os_family' ] === 'macintosh',
			'linux'		=> $os[ 'os_family' ] === 'linux',
			'windows'	=> $os[ 'os_family' ] === 'windows',
			'android'	=> $os[ 'os_family' ] === 'android',
			// 'ios'		=> $os[ 'os_family' ] === 'macintosh',
		];
	}
	
	public static function getAll() : mixed {
		return Agent::browser()->getAll( Agent::$userAgent );
	}
	
	public static function getOS() : mixed {
		return Agent::$os ??= Agent::browser()->getOS( Agent::$userAgent );
	}
	
	public static function getBrowser() : mixed {
		return Agent::browser()->getBrowser( Agent::$userAgent );
	}
}