#!/usr/local/bin/php
<?

include_once( dirname(__FILE__) . "/../base/Base.php");


$fs = new FileSystem();

$ev = new EntityVerboser( $fs );


if ( isset($argv[1]) )
	$dir = realpath( strtolower($argv[1]) );

if (!$dir)
	$dir = getcwd();
	
	
$ev->verbose( $dir );




?>