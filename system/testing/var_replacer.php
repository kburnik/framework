<?
$s = file_get_contents("_template.txt");
#echo $s;
$x = preg_match_all("/\\$([A-Za-z_]{1,})/u",$s,$r);

$vars = array_unique($r[0]);



$s = preg_replace("/\\/\\/([^\n]{0,})\n/","",$s);
$s = str_replace("\t","",$s);
$s = str_replace("\r\n\r\n","",$s);
$s = str_replace("\r\n","",$s);


// $repl = array();
$i = 0;
foreach ($vars as $key=>$var) {
	$h = dechex($i);
	$repl[$var] = "\$x{$h}";
	$i++;
}

unset($repl['$this']);
$out = strtr($s,$repl);


$out = strtr($out,array(
	"false"=>0,
	"true"=>1
));

echo $out;


?>