<?php

namespace Northrook\Support;

use Northrook\Support\Debug\Log;
use Psr\Log\LogLevel;

if ( !function_exists( 'dump' ) ) {
	function dump( mixed...$vars ) : void {
		$dump = [];
		foreach ( $vars as $var ) {
			$dump[] = print_r( $var, true );
		}

		echo implode( PHP_EOL, $dump );
	}
}

final class Debug
{

	private static array $sessionLogs = [];

	private static string $env = 'dev';

	/**
	 * Match against the current environment
	 *
	 *
	 * @param  string  $is  = [ 'dev', 'prod' ][$any]
	 * @return bool
	 */
	public static function env( string $is ) : bool {
		return Debug::$env === strtolower( $is );
	}

	public static function setEnv( mixed $APP_ENV ) : void {
		Debug::$env = strtolower( $APP_ENV );
	}

	public static function consoleLog( string $message ) : void {
		echo '<script>console.log("' . $message . '");</script>';
	}

	public static function log( string $message, mixed $dump = null, ?LogLevel $severity = null ) : void {

		$dump ??= debug_backtrace( DEBUG_BACKTRACE_PROVIDE_OBJECT, 3 );

		Debug::$sessionLogs[] = new Log\Entry(
			$message,
			$dump,
			$severity
		);

//		Debug::$sessionLogs[] = [
//			'message'  => $message,
//			'dump'     => $dump,
//			'severity' => $severity,
//		];
	}

	public static function getLogs() : array {
		return Debug::$sessionLogs;
	}

	public static function dumpLogs() : void {
		if ( !Debug::$sessionLogs ) {
			return;
		}

		dump( Debug::$sessionLogs );
	}

	public static function handleError( string | callable $do, string $message = '' ) {
		if ( is_callable( $do ) ) {
			return $do( $message );
		}
		else {
			trigger_error( $do, E_USER_ERROR );
		}

	}

	public static function dump( mixed...$vars ) : void {

		echo <<<HTML
		<head>
			<title>Debug</title>
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

//
// function consoleDump( ...$_ ) : void {
//
//     if ( $_ ) $_ = toJSON( $_ );
//
//     echo "<script>console.log(";
//     echo $_;
//     echo ")</script>";
// }

// function pd( ...$var ) : void {
//     DocumentController::$pretty = false;
//     echo PHP_EOL;
//     foreach ( $var as $v ) {
//         echo( replaceEach( [
//             "Array\n" => '[] => ',
//         ], print_r( $v, true ) ) );
//     }
//     echo PHP_EOL;
// }
//
//
// function pdp( ...$var ) : void {
//     DocumentController::$pretty    = false;
//     $debug                        = [];
//     $debug[]                    = '<pre style="margin: 0; z-index: 100">';
//     $debug[]                    = '<code style="display: block; padding: 8px">';
//
//     foreach ( $var as $v ) {
//         $debug[] = trim( replaceEach( [
//             "Array\n"    => '[] => ',
//             "\n("        => ' (',
//             // "  \n("        => " (",
//             "\t"        => '  ',
//             "    "        => '  ',
//
//         ], print_r( $v, true ) ) );
//     }
//
//     $debug[]    = '</code>';
//     $debug[]    = '</pre>';
//
//     DocumentController::addDebug( implode( '', $debug ) );
//
// }