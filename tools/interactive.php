#!/usr/bin/env php
<?
include_once( dirname(__FILE__)."/.tools.php" );
while( $line = readline() )
{
  eval("\$res = $line;");
  echo colored( var_export($res,true) , "yellow" );
  echo "\n---\n\n";
}

?>