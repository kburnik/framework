#!/usr/bin/env php
<?
include_once( dirname(__FILE__) . "/../utility/PhpCodeFormatter.php");

$file = $argv[1];
if (!file_exists($file))
  exit(-1);


$code = file_get_contents($file);
$formater = new PhpCodeFormatter();
$formater->format($code);

?>