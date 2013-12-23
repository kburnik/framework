#!/usr/local/bin/php
<?
include_once( dirname(__FILE__) . '/../testproject/project.php' );

array_shift( $argv );

TestUnitModule::run( $argv );

?>