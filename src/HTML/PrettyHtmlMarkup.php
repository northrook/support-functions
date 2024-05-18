<?php

namespace Northrook\Support\HTML;

use Northrook\Core\Interface\Printable;
use Northrook\Support\Regex;
use Northrook\Support\Str;

class PrettyHtmlMarkup implements Printable
{
    private const OPERATOR = '[%OPERATOR%]';
    private const FUSE     = '[%FUSE%]';

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

    private array $element  = [];
    private array $elements = [];
    private array $scripts  = [];

    private array $nodes;

    private array $captureAllEnclosed = [
        'script',
    ];

    public function __construct( private string $html, public bool $squish = true ) {
        $this->safelyStoreScripts();
        $this->explodeDocument();
        $this->parseDocumentElements();
        $this->constructDocument();
    }

    final public function print() : string {
        return $this->html;
    }

    /**
     * Parses the HTML string and returns a string representation of the parsed HTML.
     *
     * @return string
     */
    public function __toString() : string {
        return $this->html;
    }

    public static function pretty( string $html ) : string {
        return ( new self( $html ) )->html;
    }

    public static function restorePassedVariables( string $html ) : string {
        return str_ireplace( static::OPERATOR, '->', $html );
    }

    public static function protectPassedVariables( string $html ) : string {
        return preg_replace_callback(
            "/\\\$[a-zA-Z?>._':$\s\-]*/m",
            static fn ( array $m ) => str_replace( '->', static::OPERATOR, $m[ 0 ] ),
            $html,
        );
    }

    private function parseDocumentElements() : void {
        $skipNext = false;
        foreach ( $this->element as $key => $value ) {
            // var_dump( $value );
            if ( $skipNext ) {
                $skipNext = false;
                continue;
            }

            $set = min( strpos( $value, ' ' ), strpos( $value, '>' ) ) ?: null;
            $tag = trim( substr( $value, 0, $set ), '<!-/>' );

            $closing = str_starts_with( $value, "</$tag" );

            $type = 'line';

            if ( in_array( $tag, self::$inline, true ) ) {
                $type = 'inline';
            }

            // > If the tag is effectively empty
            if (
                !$this->isClosing( $value, $tag )
                &&
                $this->isClosing( $this->element[ $key + 1 ] ?? null, $tag )
            ) {
                $value .= $this->element[ $key + 1 ];
                $value = str_replace( '> <', '><', $value );
                // var_dump( $value );
                // $inline   = true;
                $type     = 'inline';
                $skipNext = true;
            }
            else {
                if ( $this->isText( $value ) ) {
                    $type = 'inline';
                }
                else {
                    if ( $closing ) {
                        $type = 'closing';
                    }
                    else {
                        if ( !$tag ) {
                            $type = 'comment';
                        }
                        else {
                            if ( str_starts_with( $tag, 'script:' ) ) {
                                $type = 'script';
                            }
                        }
                    }
                }
            }

            $this->elements[ $key ] = (object) [
                'tag'     => $tag,
                'type'    => $type,
                'element' => $value,
            ];

        }

        // print_r( $this->element );

        // Debug::print( $this->elements );
    }

    private function isClosing( ?string $match, ?string $current = null ) : bool {
        return str_starts_with( $match, "</$current" );
    }

    private function isText( ?string $match ) : bool {
        return !str_contains( $match, '<' );
    }

    private function constructDocument() : void {
        $skip  = [
            'DOCTYPE',
            'html',
            'head',
            'body',
        ];
        $out   = [];
        $level = 0;

        foreach ( $this->elements as $node ) {

            if ( $node->tag === 'head' ) {
                $level = ( $node->type !== 'closing' ) ? +1 : -1;
            }

            if ( in_array( $node->tag, $skip, true ) ) {
                $bump  =
                    ( $node->tag === 'body' || ( $node->type !== 'closing' && $node->tag === 'head' ) ) ? PHP_EOL : '';
                $out[] = $bump . $node->element;

            }
            else {
                if ( $node->type === 'script' ) {
                    $key    = (int) filter_var( $node->element, FILTER_SANITIZE_NUMBER_INT );
                    $script = $this->scripts[ $key ] ?? null;
                    $out[]  = $this->indent( $level ) . $script;
                }
                else {
                    if ( $node->type === 'script' ) {
                        // $fuse	= $this::FUSE;
                        $key    = (int) filter_var( $node->element, FILTER_SANITIZE_NUMBER_INT );
                        $script = $this->scripts[ $key ] ?? null;
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
                        $script = array_filter( explode( PHP_EOL, $script ) );
                        $indent = 0;
                        foreach ( $script as $line ) {
                            if ( $indent && ( str_ends_with( $line, '}' ) || $line === ");" ) ) {
                                $indent--;
                            }

                            $out[] = $this->indent( $indent ) . trim( $line );
                            if ( str_ends_with( $line, '{' ) || str_ends_with( $line, '(' ) ) {
                                $indent++;
                            }

                        }
                    }
                    else {
                        if ( $node->type === 'inline' ) {
                            $out[] = $this->indent( $level ) . trim( $node->element );

                        }
                        else {
                            if ( $node->type === 'comment' ) {
                                $out[] = PHP_EOL . $this->indent( $level ) . trim( $node->element );

                            }
                            else {
                                if ( $node->type === 'line' ) {
                                    $out[] = $this->indent( $level ) . trim( $node->element );
                                    $level++;

                                }
                                else {
                                    if ( $node->type === 'closing' ) {
                                        if ( $level ) {
                                            $level--;
                                        }

                                        $out[] = $this->indent( $level ) . trim( $node->element );

                                    }
                                    else {
                                        $out[] = $this->indent( $level ) . trim( $node->element );
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }


        $this->html = implode( PHP_EOL, $out );

        $this->html = static::restorePassedVariables( $this->html );

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
            ) as $key => $script
        ) {
            if ( !trim( str_replace( "script>\n", 'script>', $script->js ) ) ) {
                continue;
            }

            $this->scripts[ $key ] = $script->matched;
            $this->html            = str_replace(
                $script->matched,
                "<script:[$key]>",
                $this->html,
            );
        }
    }

    private function explodeDocument() : void {
        $this->html    = $this->squish ? Str::squish( $this->html ) : $this->html;
        $this->html    = static::protectPassedVariables( $this->html );
        $fuse          = $this::FUSE;
        $document      = Str::replaceEach(
            [
                '>'           => '>' . $fuse,
                '<'           => $fuse . '<',
                "$fuse $fuse" => $fuse,
                "$fuse$fuse"  => $fuse,
                ' />'         => '/>',
            ],
            $this->html,
        );
        $explode       = explode( $fuse, $document );
        $this->element = array_filter( $explode, 'trim' );
    }
}