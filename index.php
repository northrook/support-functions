<?php
require __DIR__ . '/vendor/autoload.php';

use Northrook\Support\File;
use Northrook\Support\HTML\Element;
use Northrook\Support\HTML\Render;
use Northrook\Support\Str;

class Test { }

;

// dd( phpinfo() );

var_dump(
    new Test(),
    Str::normalizePath( __DIR__ . '/assets/example.html' ),
    Str::filepath( __DIR__ . '/assets/example.html', __DIR__ . '/assets/example.html' ),
);

echo "\n";
echo "<!DOCTYPE html>\n";
$title   = 'Hello  __ World';
$classes = [ 'heading', 'title' ];
echo File::makeDirectory( __DIR__ . '/assets/example.html' );
echo '<hr>';
echo File::getFileName( __DIR__ . '/assets/example.html', true );
echo '<hr>';
echo File::getDirectoryPath( __DIR__ . '/assets/example.html' );

echo '<hr>';
echo new Element(
    'h1', [
    'class'         => $classes,
    'id'            => $title,
    'data-foo'      => 'bar',
    'dat DING:DONG' => 'bar',
    'style'         => [ 'color: deeppink', 'background-color: deeppink', 'background-color: orange' ],
    'disabled'      => true,
],  'Hello World'
);
echo "\n";

// echo Render::new( 'h1', [
//     'class'			=> $classes,
// ] , 'Hello World' );

print_r( Element::classes( 'test' ) );
// echo '<hr>';
// echo htmlspecialchars( Str::key( $title, trim : true ) );
// echo '<hr>';
// echo htmlspecialchars( Str::toCamel( $title ) );
// echo '<hr>';
// echo htmlspecialchars( Str::slug( $title, trim : true ) );

// echo Str::filepath( __FILE__ . '/src/*.php' );
//
//
$key = <<<CSS
div.Description_Productinfo div.or-s {
  display: none;
}

div.Description_Productinfo > div:nth-child(4n+2)::after {
  content: '';
  position: absolute;
  top: 0;
  left: -50vw;
  height: 100%;
  width: 100vw;
  background: #E6E6E5;
  z-index: 0;
  pointer-events: none;
}

div.Description_Productinfo > div.descriptionContainer {
  position: relative;
  display: flex;
  gap: 2rem;
}

div.Description_Productinfo > div.descriptionContainer > * {
  display: flex;
  flex-direction: column;
  flex: 1 1 40%;
  z-index: 1;
}


div.Description_Productinfo > div.descriptionContainer > *:not(.text) {
    align-items: center;
}

div.Description_Productinfo > div.descriptionContainer .text {
  padding: 2rem;
}
CSS;
echo '<hr>';
var_dump( str_replace( ':', '__', [ $key, $key ] ) );
// print Str::containsAll( $key, [ ':', '{', '}', '__' ] ) ? 'true' : 'false';
//
// Northrook\Support\HTML\Render::template( file_get_contents( '_document.html.twig' ) );
//
// echo '<hr>';
// $squish = Str::squish( file_get_contents( '_document.html.twig' ), spacesOnly : true );
// print ( $squish );
// echo '<hr>';