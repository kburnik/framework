<?
include_once("config.php");

$c = get_defined_constants();
foreach ($c as $key=>$val) {
	if (substr($key,0,2)!='__') unset($c[$key]);
}

print_r($c);

print_r($_SERVER);

?>