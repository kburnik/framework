#!/usr/bin/env php
<?
include_once(dirname(__FILE__) . "/../utility/PhpCodeFormatter.php");

$file = $argv[1];
if (! file_exists($file))
  exit(- 1);


$code = file_get_contents($file);
$formatter = new PhpCodeFormatter();
$reformatted = $formatter->format($code);
file_put_contents($file, $reformatted);
?>