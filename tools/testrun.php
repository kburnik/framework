#!/usr/local/bin/php
<?
include_once( dirname(__FILE__) . '/../base/Base.php' );
@include_once( dirname(__FILE__) . '/../testproject/project.php' );

array_shift( $argv );


Console::Disable();

TestUnitModule::run( $argv );

?>