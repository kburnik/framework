<?
if (substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) ob_start("ob_gzhandler"); else ob_start();

function str_squeeze($str) {
	$str = preg_replace('/([\r\n]{1,})/'," ",$str);
	$str = preg_replace('/([\n]{1,})/'," ",$str);
	$str = preg_replace('/; /',';',$str);
	$str = preg_replace('/{ /','{',$str);
	$str = preg_replace('/ }/','}',$str);
	return preg_replace('/([\ ]{2,})/',' ',$str);
}
function remove_comments($str) {
	return trim(preg_replace('#/\*(?:[^*]*(?:\*(?!/))*)*\*/#',' ',$str));
}

if ($_GET) {
	$orig_filename = $_GET['filename'];
	$filename = dirname(__FILE__)."/../".$orig_filename;
}

$start = round(microtime(true)*1000);
$contents = file_get_contents($filename);
$fs = round(filesize($filename));

$contents = str_replace("\t","",$contents);
$contents = str_squeeze($contents);
$contents = remove_comments($contents);

$expires = 60*60*1;
header('Content-type: text/css');
header("Cache-Control: maxage=".$expires);
header('Expires: ' . gmdate('D, d M Y H:i:s', time()+$expires) . ' GMT');


$worktime = (round(microtime(true)*1000)-$start);
$size = round(strlen($contents));

$ratio = round($size*100/$fs,2);
//$signature  = "/* $orig_filename :: Compressed $ratio% @ $worktime ms :: CSS minifer BETA by Kristijan Burnik (C) 2010: */\n";
die($signature.$contents);
?>