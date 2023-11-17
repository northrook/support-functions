<?php

/** @noinspection ForgottenDebugOutputInspection */

namespace Northrook\Support;

use JetBrains\PhpStorm\NoReturn;


if ( ! function_exists( 'dump' ) ) {
	function dump( mixed ...$vars ) : void {
		$dump = [];
		foreach ( $vars as $var ) $dump[] = print_r( $var, true );
		echo implode( PHP_EOL, $dump );
	}
}


final class Debug {
	
	
	#[NoReturn]
	public static function dump( mixed ...$vars ) : void {
		
		echo <<<HTML
		<head>
			<title>Debug</title/>
			<style>
			*, *:before, *:after {
				box-sizing: border-box;
			}
				html, body {
					margin: 0;
					padding: 0;
					background-color: #18171B;
				}
				body pre.sf-dump {
					margin: 0;
					padding: 15px 5px;
				}
				body pre.sf-dump:first-of-type{
					padding-top: 5px;
				}
				body pre.sf-dump:not(:first-of-type) {
					border-top: 1px solid #A0A0A0;
				}
				body pre.sf-dump span.sf-dump-index {
					display: inline-block;
					min-width: 4ch;
					text-align: right;
					margin-left: -2ch;
				}
				
				body pre.sf-dump,
				body pre.sf-dump .sf-dump-default {
					color: #6d6d6d;
				}
				body pre.sf-dump .sf-dump-note {
					color: #3370FF;
				}
				body pre.sf-dump .sf-dump-index {
					color: #4CFFFF;
				}
				body pre.sf-dump .sf-dump-label {
					color: #004CFF;
				}
				body pre.sf-dump .sf-dump-str {
					color: #D4B2FF;
					font-weight:normal;
					opacity: .5;
				}
				
				body pre.sf-dump .sf-dump-str::before {
				/*body pre.sf-dump .sf-dump-index::before {*/
					position:absolute;
					content: '>';
					left:  0;
					right: 0;
					color: transparent;
					z-index: 0;
					/*background-color: #3b82f6;*/
					/*pointer-events: bounding-box;*/
				}
			
			
				body pre.sf-dump .sf-dump-str:hover {
					opacity: 1;
				}
				
				body pre.sf-dump .sf-dump-str:hover::before {
					color: #f2f2f2;
				}
				
				body pre samp.sf-dump-expanded span {
					padding: 1px 0;
				}
			</style>
		</head>
		HTML;
		echo '<body>';
		dump( ...$vars );
		echo '</body></html>';
		
		
		die( 1 );
	}
}