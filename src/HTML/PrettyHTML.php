<?php

namespace Northrook\Support\HTML;

use Northrook\Support\Debug;
use Northrook\Support\Regex;
use Northrook\Support\Str;

/**
 * @stability	prototype
 * @package		Northrook\Support
 * @version		alpha
 * @return string
 */
final class PrettyHTML {
	
	private const FUSE = '[%FUSE%]';
	private static array $inline = [
		'br',
		'hr',
		'img',
		'input',
		'link',
		'meta',
		'param',
		'source',
		'track',
		'wbr',
		'circle',
		'path',
		'rect',
		'line',
		'polyline',
		// 'span',
	];
	
	private array	$element	= [];
	private array	$elements	= [];
	private array	$scripts	= [];
	
	private array $nodes;
	
	private array $captureAllEnclosed = [
		'script',
	];
	
	/** @return string  */
	public function __toString() : string {
		// $metrics = null;
		// if ( ENV === 'dev' ) {
		// 	/** @noinspection PhpUndefinedConstantInspection */
		// 	$log = [
		// 		// 'WP_INIT' => Time::stopwatch( 'ms', hrtime : WP_INIT ),
		// 		'FW_INIT' => Time::stopwatch( 'ms', hrtime : FW_INIT ),
		// 	];
		// 	foreach ( Framework::$mark as $note => $time ) {
		// 		$log[ $this::lastWord( $note, '\\' ) ] = $time;
		// 	}
		//
		// 	$key = $this::ceil( $log, 'max', true );
		//
		// 	$metrics = [];
		// 	foreach ( $log as $note => $ms ) {
		// 		$metrics[] = "<!-- " . str_pad( $note, $key ) . " $ms -->";
		// 	}
		// 	$metrics = PHP_EOL . $this::implode( $metrics, PHP_EOL ) . PHP_EOL . PHP_EOL;
		// }
		return (string) $this->html;
	}

    public static function string (string $html) : string {
        return (new self($html))->html;
    }
	
	public function __construct( private string $html, public bool $squish = true ) {
		// $this->stopwatch = hrtime( true );
		$this->safelyStoreScripts();
		$this->explodeDocument();
		$this->parseDocumentElements();
		$this->constructDocument();
	}
	
	
	private function parseDocumentElements() : void {
		$skipNext = false;
		foreach ( $this->element as $key => $value ) {
			// var_dump( $value );
			if ( $skipNext ) {
				$skipNext = false;
				continue;
			}
			
			$set	= min( strpos( $value, ' ' ), strpos( $value, '>' ) ) ?: null;
			$tag	= trim( substr( $value, 0, $set ), '<!-/>' );
			
			$closing = str_starts_with( $value, "</$tag" );
			
			$type = 'line';
			
			if ( in_array( $tag, PrettyHTML::$inline, true ) ) {
				$type = 'inline';
			}
			
			
			// > If the tag is effectively empty
			if (
				! $this->isClosing( $value, $tag )
				&&
				$this->isClosing( $this->element[ $key + 1 ] ?? null, $tag )
			) {
				$value	.= $this->element[ $key + 1 ];
				$value	= str_replace( '> <', '><', $value );
				// var_dump( $value );
				// $inline   = true;
				$type		= 'inline';
				$skipNext	= true;
			}
			elseif ( $this->isText( $value ) ) {
				$type = 'inline';
			}
			elseif ( $closing ) {
				$type = 'closing';
			}
			elseif ( ! $tag ) {
				$type = 'comment';
			}
			elseif ( str_starts_with( $tag, 'script:' ) ) {
				$type = 'script';
			}
			
			$this->elements[ $key ] = (object) [
				'tag'		=> $tag,
				'type'		=> $type,
				'element'	=> $value,
			];
			
			
		}
		
		// print_r( $this->element );
		
		// Debug::print( $this->elements );
	}
	
	private function isClosing( ?string $match, ?string $current = null ) : bool {
		return str_starts_with( $match, "</$current" );
	}
	
	private function isText( ?string $match ) : bool {
		return ! str_contains( $match, '<' );
	}
	
	private function constructDocument() : void {
		$skip	= [
			'DOCTYPE',
			'html',
			'head',
			'body',
		];
		$out	= [];
		$level	= 0;
		
		foreach ( $this->elements as $node ) {
			
			if ( $node->tag === 'head' ) $level = ( $node->type !== 'closing' ) ? + 1 : - 1;
			
			if ( in_array( $node->tag, $skip, true ) ) {
				$bump	= ( $node->tag === 'body' || ( $node->type !== 'closing' && $node->tag === 'head' ) ) ? PHP_EOL : '';
				$out[]	= $bump . $node->element;
				
			}
			elseif ( $node->type === 'script' && Debug::env( 'prod' ) ) {
				$key	= (int) filter_var( $node->element, FILTER_SANITIZE_NUMBER_INT );
				$script	= $this->scripts[ $key ] ?? null;
				$out[]	= $this->indent( $level ) . $script;
			}
			elseif ( $node->type === 'script' ) {
				// $fuse	= $this::FUSE;
				$key	= (int) filter_var( $node->element, FILTER_SANITIZE_NUMBER_INT );
				$script	= $this->scripts[ $key ] ?? null;
				// if ( ) {
				// 	$script = $this::replaceEach(
				// 		[
				// 			';'              => ";$fuse",
				// 			' function'      => "{$fuse}function",
				// 			'{'              => "{ $fuse",
				// 			'}'              => "}$fuse",
				// 			"}$fuse,{ $fuse" => "},{",
				// 		],
				// 		$script
				// 	);
				// }
				$script	= array_filter( explode( PHP_EOL, $script ) );
				$indent	= 0;
				foreach ( $script as $line ) {
					if ( $indent && ( str_ends_with( $line, '}' ) || $line === ");" ) ) $indent --;
					$out[] = $this->indent( $indent ) . trim( $line );
					if ( str_ends_with( $line, '{' ) || str_ends_with( $line, '(' ) ) $indent ++;
				}
			}
			elseif ( $node->type === 'inline' ) {
				$out[] = $this->indent( $level ) . trim( $node->element );
				
			}
			elseif ( $node->type === 'comment' ) {
				$out[] = PHP_EOL . $this->indent( $level ) . trim( $node->element );
				
			}
			elseif ( $node->type === 'line' ) {
				$out[] = $this->indent( $level ) . trim( $node->element );
				$level ++;
				
			}
			elseif ( $node->type === 'closing' ) {
				if ( $level ) $level --;
				$out[] = $this->indent( $level ) . trim( $node->element );
				
			}
			else {
				// var_dump( $node->element );
				$out[] = $this->indent( $level ) . trim( $node->element );
			}
		}
		
		$this->html = implode( PHP_EOL, $out );
		
	}
	
	private function indent( int $element ) : string {
		$indent = ( $element <= 0 ) ? 0 : $element;
		return str_repeat( "\t", $indent );
	}
	
	private function safelyStoreScripts() : void {
		foreach (
			Regex::matchNamedGroups(
				"/(?<script><script.*?>(?<js>.*?)<\/script>)/ms",
				$this->html,
			)
			as $key => $script
		) {
			if ( ! trim( str_replace( "script>\n", 'script>', $script->js ) ) ) continue;
			// var_dump( $script );
			$this->scripts[ $key ]	= $script->matched;
			$this->html				= str_replace(
				$script->matched,
				"<script:[$key]>",
				$this->html
			);
		}
	}
	
	private function explodeDocument() : void {
		$this->html		= $this->squish ? Str::squish( $this->html ) : $this->html;
		$fuse			= $this::FUSE;
		$document		= Str::replaceEach(
			[
				'>'				=> '>' . $fuse,
				'<'				=> $fuse . '<',
				"$fuse $fuse"	=> $fuse,
				"$fuse$fuse"	=> $fuse,
				' />'			=> '/>',
				// ' />'         => '>',
			],
			$this->html
		);
		$explode		= explode( $fuse, $document );
		$this->element	= array_filter( $explode, 'trim' );
	}
}