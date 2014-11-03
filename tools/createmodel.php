#!/usr/local/bin/php
<?

include_once( dirname(__FILE__) . "/../base/Base.php");


# $templateDir = dirname( __FILE__  ) . "/templates" ;
# $destinationDir = getcwd();

$fs = new FileSystem();

$mc = new EntityModelCreator( $fs );

array_shift( $argv );

foreach ( $argv as $entityName )
  $mc->createModel( $entityName );


# print_r( $fs->files );



?>