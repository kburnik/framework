#!/usr/local/bin/php
<?

include_once( dirname(__FILE__) . "/../base/Base.php");


$fs = new FileSystem();

$ev = new EntityBuilder( $fs );

$ev->resolveProject( $fs->getcwd() );

if ( isset($argv[1]) )
	$dir = $fs->realpath( strtolower($argv[1]) );

if (!$dir)
	$dir = $fs->getcwd();
	
	
	
$dataDriver = new MySQLDataDriver();

	
$ev->build( $dir , $dataDriver );




?>