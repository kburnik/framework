<?

// return execution time in seconds // or set
function execution_time($star_as_of_now = false) {
	static $execution_time = -1;
	if ($execution_time < 0 || $star_as_of_now) $execution_time = microtime(true);
	return round(microtime(true)-$execution_time);
}

// auxliary array functions and DATA transformations
if (!function_exists("is_assoc")) {
	function is_assoc($array) {
	    return (is_array($array) && (0 !== count(array_diff_key($array, array_keys(array_keys($array)))) || count($array)==0));
	} 
}

function rotate_table($data) { // rotates the structure from [{a:1,b:2},{a:2,b:3}] to {a:[1,2],b:[2,3]}
	if (count($data)==0 || count(reset($data))==0) return false;
	
	$out = array();
	$fields = array_keys(reset($data));
	foreach($data as $index=>$row) {
		foreach ($row as $field=>$value) {
			$out[$field][$index] = $value;
		}
	}
	
	return $out;
}

function array_pick($array,$keys=array()) {
	$data = array();	
	if (is_string($keys)) $keys = explode(",",$keys);
	
	if (count($keys)>0) 
	{
		return array_intersect_key( $array , array_combine( $keys, $keys ) );
	} 
	else 
	{
		return $array;
	}	
}

function csv2arr($csv,$delimiter='|',$header_end_marker='|;') {
	$header_data_mixed = explode($header_end_marker,$csv);
	$header = reset($header_data_mixed);
	$header = explode("|",$header);
	array_shift($header);
	$column_count = count($header);
	
	array_shift($header_data_mixed);
	$data_mixed =  $header_data_mixed;
	
	$data = implode($header_end_marker,$data_mixed);
	
	$delimiter="\\".$delimiter;
	$pattern ="/{$delimiter}(([^{$delimiter}]{0,})[{$delimiter}]){{$column_count},{$column_count}}[\r\n]{0,}/";
	preg_match_all($pattern,$data,$matches);
	
	if (count($matches[0])>0) {
		foreach ($matches[0] as $rowindex=>$row) {
			$row = explode("|",$row);
			array_shift($row);
			array_pop($row);
			foreach ($row as $columindex=>$field) {
				$out[$rowindex][$header[$columindex]] = $field;
			}
		}
	}
	return $out;
}

function arr2csv($arr,$delimiter='|',$header_end_marker='|;',$linebreak="\n") {
	if (count($arr)==0 ||count($arr[0])==0) return "";
	$arr_header = array_keys($arr[0]);
	$header = $delimiter.implode($delimiter,$arr_header).$header_end_marker.$linebreak;
	$data = $header;
	foreach ($arr as $index=>$arr_row) {
		$data .= $delimiter.implode($delimiter,$arr_row).$delimiter.$linebreak;
	}
	return $data;
}


function run_function_vector($functions,$parameter) { // modifies parameter by passing it to all listed functions enclosed: e.g. strotlower(trim($var))
	if (!is_array($functions)) $functions = explode(",",$functions);
	foreach ($functions as $function) {
		if (function_exists($function)) {
			$parameter = $function($parameter);
		} else {
			trigger_error("Function <strong>{$function}</strong> doesn't exist!",E_USER_WARNING);
			return false;
		}
	}
	return $parameter;
}

function apply_transformations($row,$transformations) {
	if (is_assoc($transformations)) {
		foreach ($transformations as $field=>$functions) {
			$row[$field] = run_function_vector($functions,$row[$field]);
		}
	} else {
		foreach ($row as $field=>$f) {
			$row[$field] =  run_function_vector($transformations,$f);
		}
	}
	return $row;
}


// timing + stats functions
function micronow() {
	return (microtime(true)*1000);
}

function microdiff($t) {
	return micronow()-$t;
}

function now($format="Y-m-d H:i:s") {
	return date($format);
}

// string secure and mysql secure
function secure($value,$strip_tags=false,$strip_slashes=true) { // MySQL preinsert/preupdate secure
	if (is_array($value)) return $value;
	if ($strip_slashes) $value=str_replace("\\","",$value);
	if ($strip_tags) $value=strip_tags($value);
	$value=str_replace("'","''",$value);
	$value=str_replace("\\","\\\\",$value);
	return $value;
}

// REGEX functions // TODO :: place all to REGEX module
function is_valid_email($email) {
  if(preg_match("/[.+a-zA-Z0-9_-]+@[a-zA-Z0-9-]+.[a-zA-Z]+/", $email) > 0)
	return true;
  else
	return false;
}




// string functions, UTF-8 and croatian issues
function toupper($s) {
    return mb_strtoupper($s,"utf8");
}

function tolower($s) {
    return mb_strtolower($s,"utf8");
}

function fcefilter($q) {
	$q=str_replace('æ','c',$q);
    $q=str_replace('š','s',$q);
    $q=str_replace('ð','d',$q);
    $q=str_replace('è','c',$q);
	$q=str_replace('ž','z',$q);   
	
	$q=str_replace('Æ','C',$q);
    $q=str_replace('Š','S',$q);
    $q=str_replace('Ð','D',$q);
    $q=str_replace('È','C',$q);
	$q=str_replace('Ž','Z',$q);   
	
	return $q;
}



// tags
function javascript($js,$tabs=0) {
	$t = str_repeat("\t",$tabs);
	if (!is_array($js)) {
		$out = "{$t}<script type=\"text/javascript\" src=\"{$js}\"></script>";
	} else {
		foreach ($js as $j) {
			$out[] = javascript($j,$tabs);
		}
	}
	if (is_array($out)) $out =implode("\n",$out);
	return $out;
}

function jscode($code) {
	return "<script type=\"text/javascript\">{$code}</script>";
}

function css($css,$tabs=0) {
	$t = str_repeat("\t",$tabs);
	if (!is_array($css)) {
		$out = "{$t}<link href=\"{$css}\" rel=\"stylesheet\" type=\"text/css\" />";
	} else {
		foreach ($css as $c) {
			$out[] = css($c,$tabs);
		}
	}

	if (is_array($out)) $out = implode("\n",$out);
	return $out;
}



// IDS, hashes and passwords
function passwordhash($pwd,$chunk='chunk') {
	return md5($pwd.$chunk);
}

function randomhash($date_format="YmdHis",$rand_range=1000) {
	return md5(date($date_format).md5(rand(0,$rand_range)));
}

// content functions
function shorten($title,$maxlength='100') {
	if (strlen($title)>$maxlength) {
		$title=substr($title,0,$maxlength);
		$title=substr($title,0,strlen($title)-strpos(strrev($title),' ',0))."...";	
	}
	return $title;
}

// todo: translate croatian chars to eng chars!
function keyword_name($string) {
	for ($i=0; $i<strlen($string); $i++) {
		$string[$i]= (preg_match("/[A-Za-z0-9_.()\[\]]/",$string[$i])) ? $string[$i] : '_';
	}
	return $string;
}


// FILE functions : local and remote // TODO: place to FILESYSTEM module
function get_ext($filename) {
	$filename=basename($filename);
	$x=strpos(strrev($filename),".");
	return $ext=strtolower(substr($filename,-$x));	
}


function uploadfile($file,$dir='photos/',$newname=false) {
	$uploaddir=$dir."/";	
	$basename=keyword_name(strtolower(basename($file['name'])));
	
	if (file_exists("{$uploaddir}{$basename}")) {				
		$d = dir("$uploaddir");		
		$k=0;
		while (false !== ($cf = $d->read())) $k++;			   
		$d->close();				
		$basename=$k."_".$basename;
	}
	$uploadfile = $uploaddir . $basename;
	if (move_uploaded_file($file['tmp_name'], $uploadfile)) {
		if ($newname) {
			$newname=$newname.".".get_ext($basename);
			rename($uploadfile,$uploaddir.$newname); $basename=$newname;
		}
		return $basename;
	}
	
	return false;
}

function getcontents($source_url,$domain="localhost") {
	if (strpos($source_url,"http://")>=0) {
		return curl_getcontents($source_url);
	}
	if (@fopen($source_url, "r")) {
		if (strpos($source_url,"http://")>=0) {
			$handle = fopen($source_url, "rb");
			$contents = stream_get_contents($handle);
			fclose($handle);
		} else {
			$contents = file_get_contents($source_url);
		}
		
	} else {
		return false;
	}

	return $contents;
}

function curl_getcontents($remoteurl,$force_refresh=false) {
	// variables to set
	//$remoteurl = "http://plus.hr"; //Url koji zelite downloadati
	$chtime = "1"; //lokalni sadrzaj nece biti stariji od koliko sati?
	$timeout = "5"; //Koliko se dugo u sekundama ceka remote server?

	//ne editirati
	$localfile = __CACHE_DIR__."/".md5($remoteurl).".cache";

	if (file_exists($localfile)){

		$localfile_stat = stat($localfile);
		if ($localfile_stat['mtime'] < strtotime("-$chtime hours") || $force_refresh){

			$chresponse = curl_init($remoteurl);
			$ret = curl_setopt($chresponse, CURLOPT_HEADER, 1);
			$ret = curl_setopt($chresponse, CURLOPT_FOLLOWLOCATION,1);
			$ret = curl_setopt($chresponse, CURLOPT_TIMEOUT,$timeout);
			$ret = curl_setopt($chresponse, CURLOPT_RETURNTRANSFER, 1);
			$ret = curl_exec($chresponse);

			if (empty($ret)) {

				die(curl_error($chresponse));
				curl_close($chresponse);
			} else {
				$info = curl_getinfo($chresponse);
				curl_close($chresponse);
				if ($info['http_code'] == "200") {

					$ch = curl_init($remoteurl);
					$fp = fopen($localfile, "w");

					curl_setopt($ch, CURLOPT_FILE, $fp);
					curl_setopt($ch, CURLOPT_HEADER, 0);
					curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
					curl_exec($ch);
					curl_close($ch);
					fclose($fp);
				} else {
					touch($localfile);
				}
			}
		}
	} else {
		$ch = curl_init($remoteurl);
		$fp = fopen($localfile, "w");
		curl_setopt($ch, CURLOPT_FILE, $fp);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_exec($ch);
		curl_close($ch);
		fclose($fp);
	}
		$handle = fopen($localfile, "r");
	fclose($handle);
	return file_get_contents($localfile);
}



function json_format($json) {
    $tab = "  ";
    $new_json = "";
    $indent_level = 0;
    $in_string = false;
   
    $json_obj = json_decode($json);
   
    if(!$json_obj)
        return false;
   
    $json = json_encode($json_obj);
    $len = strlen($json);
   
    for($c = 0; $c < $len; $c++)
    {
        $char = $json[$c];
        switch($char)
        {
            case '{':
            case '[':
                if(!$in_string)
                {
                    $new_json .= $char . "\n" . str_repeat($tab, $indent_level+1);
                    $indent_level++;
                }
                else
                {
                    $new_json .= $char;
                }
                break;
            case '}':
            case ']':
                if(!$in_string)
                {
                    $indent_level--;
                    $new_json .= "\n" . str_repeat($tab, $indent_level) . $char;
                }
                else
                {
                    $new_json .= $char;
                }
                break;
            case ',':
                if(!$in_string)
                {
                    $new_json .= ",\n" . str_repeat($tab, $indent_level);
                }
                else
                {
                    $new_json .= $char;
                }
                break;
            case ':':
                if(!$in_string)
                {
                    $new_json .= ": ";
                }
                else
                {
                    $new_json .= $char;
                }
                break;
            case '"':
                if ($json[$c-1]!='\\') $in_string = !$in_string;
            default:
                $new_json .= $char;
                break;                   
        }
    }
   
    return $new_json;
}

function showtable($data,$class='') {
	if (count($data)==0 || count($data[0])==0 || !is_array($data)) return "<br /><strong>Nema tablice</strong><br />";
	return produce(TPL_STD_TABLE,$data);
}

function pretty_filesize($size) {
	$measures = explode(",","B,KiB,MiB,GiB");
	$i=0;
	while ($size > 1024) {
		$size /= 1024;
		$i++;
	};
	$size = round($size,2)." ".$measures[$i];
	return $size;
}

function stringtoimage($str,$encrypt=true,$path='',$sep='&amp;') {
	$w = strlen($str)*6;
	$h = 14;
	
	if ($encrypt) {
		$str = encrypt($str);
		$enc="{$sep}encrypted";
	}

	
	$str = urlencode($str);
	$url = $path."system/stringtoimage.php?{$str}{$enc}";
	
	$image = "<img src=\"{$url}\" width=\"$w\" height=\"$h\" alt=\"\" class=\"stringtoimage\" />";
	return $image;
}

function success($message,$section='main',$style='') {
	if ($style) $style=' style="'.$style.'"';
	$success="<div class=\"success\"{$style}>".$message."</div>";
	if ($section) pc($success,$section);
	return $success;
}

function message($message,$section='main',$style='') {
if ($style) $style=' style="'.$style.'"';
	$message="<div class=\"neutral\"{$style}>".$message."</div>";
	if ($section) pc($message,$section);
	return $message;
}


// DEBUGING FUNCTIONS // TODO -> place to DEBUG module
$_WATCH = array();
$_ERRORS_ARRAY = array();
$log_entries=array();
$show_log=true;


function error($message,$query='',$backtrace = false) {
	global $_ERRORS_ARRAY;
	if (!$backtrace) {
		$backtrace = debug_backtrace();
		array_shift($backtrace);
	}
	
	$backtrace_out="";
	foreach($backtrace as $call) {
		unset($class);
		extract($call);
		$arguments = dim_special_chars(htmlentities(json_format(json_encode($args))));
		$function = "<span style='color:#ff4411;'>{$function}</span>";
		if ($class!='') {
			$function = "<span style='color:green;'>{$class}</span> <span style='color:#444444;'>-&gt;</span> {$function}";
		}
		$backtrace_out .= "<li style='border-bottom:1px solid #dddddd;margin-bottom:10px;padding-bottom:10px;'><span style='color:#114488;' href='$file'>{$file}</span> (line {$line})<br />{$function}(<blockquote><pre>{$arguments}</pre></blockquote>);</li>";
	}
	
	$_ERRORS_ARRAY[] = array(
		"message"=> $message,
		"backtrace"=> $backtrace,
		"query" => $query
	);
	
	if ($query!='') {
		if (strpos ($message,"SQL syntax")) {
			$start = strpos($message,"'",0);
			$end = strpos($message,"at line",$start);
			$part = trim(substr($message,$start+1,$end-$start-3));
			$query = str_replace($part,"<span style='text-decoration:underline;color:red;'>$part</span>",$query);
			print_r($parts);
		}
		$query_link = "| <a href='javascript:' onclick='document.getElementById(\"error_query_{$id}\").style.display=\"block\";'>Query</a>";
		$query_div = "<pre id='error_query_{$id}' style='padding:10px;display:none;font:14px Courier New;background:white;border:1px solid #aa8833;'>{$query}</pre>";
	}
	
	$id = count($_ERRORS_ARRAY);
	echo "<div style='padding:10px;background:#fff4f4;border:1px solid #aa8833;font:12px Arial;margin-bottom:5px;'>
		<span style='font:12px Arial;'>{$message}</span><br />
		<a href='javascript:' onclick='document.getElementById(\"error_backtrace_{$id}\").style.display=\"block\";'>Backtrace</a>
		{$query_link}
		<ol id='error_backtrace_{$id}' style='display:none;font:12px Courier New;background:white;border:1px solid #aa8833;'>{$backtrace_out}</ol>
		{$query_div}
	</div>
	
	";
	
}

function MyErrHandler($errno, $errstr, $errfile, $errline) {
	if (in_array($errno,array( E_ERROR , E_WARNING,E_USER_ERROR,E_USER_WARNING) )) {
		$backtrace = debug_backtrace();
		array_shift($backtrace);
		if (in_array($backtrace[0]['function'],array('trigger_error'))) {
				array_shift($backtrace);
		}
		error("$errstr",'',$backtrace);
		return true;
	}
	return false;
}

function watch($data=null,$name=false) {
	global $_WATCH;
	if ($data == null && !is_string($name)) {
		if ($name) {
			return $_WATCH;
		} else {
			showme($_WATCH,"WATCH");
		}
	} else {
		if ($name!=false) {
			$_WATCH[][$name] = $data;
		} else {
			$_WATCH[] = $data;
		}
	}
}

function showme($data,$name='Output',$json=true) {
	if ($json) {
		$data = htmlentities(json_format(json_encode($data)));
		$data = "<div style='background:#fafafa;border:1px solid black; padding:10px;'><pre style='font:12px Courier New;'>{$name} = {$data}</pre></div>";
		echo $data;
	} else { 
		echo $name." =";
		print_r($data);
	}
}

function dim_special_chars($str) {
	$l ="<span style='color:#eeeeee;'>";
	$r = "</span>";
	$map = array(
		'\r'=>"{$l}\\r{$r}",
		'\n'=>"{$l}\\n{$r}",
		'\t'=>"{$l}\\t{$r}",
	);
	foreach ($map as $find => $replace) {
		$str = str_replace($find,$replace,$str);
	}
	
	return $str;
}

function __log($data,$file=__FILE__,$method=__METHOD__,$line=__LINE__) {
	global $log_entries;
	
	if (is_array($data)) $data="<pre>".var_export($data,true)."</pre>";
	
	$bt=debug_backtrace(); 
    $sp=0;
    $trace="";
    foreach($bt as $k=>$v) {
        extract($v);
        $file=str_replace(__ROOT__,"",$file);
        $trace.=str_repeat("&nbsp;",++$sp); //spaces(++$sp);
        $trace.="$file -- $function ($line)<br>";       
    }
    $backtrace=$trace;

	$bg=(count($log_entries)%2==0) ? "#ffffff" : "#f4f4f4" ;
	$log_entries[]=array("data" => $data,"backtrace"=> $backtrace, "bg"=>$bg);
}

function outputlog() {
	global $log_entries,$show_log;
	if ( !$show_log ) return false;
	
	if ( ( $count = count($log_entries) ) > 0 ) {
		$log_entries = array_reverse($log_entries);
		$tpl="
			<li style='padding:5px;border-bottom:1px solid #dddddd;width:350px;background:{bg};'>
			<strong>{data}</strong><br />
			<span style='font-size:10px;color:#444444;'>{backtrace}</span>
			</li>
		";
		$logs=sd($log_entries,$tpl);
		$out="
			<ul style='height:500px;width:380px;overflow:auto;display:none;position:absolute;left:0px;padding:5px;background:white;list-style:none;border:1px solid #444444;z-index:1000;' id='log' ondblclick='$(this).hide();'>
				{$logs}
			</ul>
			<script type='text/javascript'>
				$(window).keydown(function(e){
					if (e.keyCode==113) $('#log').toggle(); // toggle display of log on F2 Press
					
				});
			</script>
		";
		pc($out,"log");
	}
}


// PAGE BUILDING FUNCTIONS : includes
function callback($contents) {
	global $included_contents;
	$included_contents=$contents;
}

function includescript($file) {
	global $included_contents;
	ob_start("callback");
	include($file);
	ob_end_flush();
	return $included_contents; 
}

// URL functions
function getencode($vars,$key=false) {
	if (!is_array($vars)) return "$key=".urlencode($vars);
	foreach($vars as $key=>$var) {
		$out[]=getencode($var,$key);
	}
	$out=implode("&amp;",$out);
	return $out;
}

function friendly_url($string){
	$wl = new WordList($string);
	return implode('-',$wl->toANSI()->toLowerCase()->getTerms());
	
	/*
	$croatian_chars = array(
		"ÄŒ"=>"c", "Ä"=>"c",
		"Ä†"=>"c", "Ä‡"=>"c",
		"Ä"=>"d", "Ä‘"=>"d",
		"Å "=>"s", "Å¡"=>"s",
		"Å½"=>"z", "Å¾"=>"z",
		">"=>" ",
		"&amp;"=>"",
		"&quot;"=>"",
		"&gt;"=>" ",
		"&lt;"=>" ",
		"\""=>"",
	);
	$string = strip_tags($string);
	$string = preg_replace( "`&([a-z])(acute|uml|circ|grave|ring|cedil|slash|tilde|caron|scaron|lig|quot|rsquo|t|);`i","\\1", $string );
	$string = strtr($string,$croatian_chars);
	$string = preg_replace("`\[.*\]`U","",$string);
	$string = preg_replace('`&(amp|lt|gt;)?#?[a-z0-9]+;`i','-',$string);
	$string = htmlentities($string, ENT_COMPAT, 'utf-8');
	$string = preg_replace( array("`[^a-z0-9]`i","`[-]+`") , "-", $string);
	return strtolower(trim($string, '-'));
	*/
}

//// server data for using with javascript:
$SERVER_DATA=array();
$SERVER_DATA_OUTPUT=false;
function server_data($data=null,$options=null) {
	global $SERVER_DATA,$SERVER_DATA_OUTPUT;
	
	
	// output the server data
	if ($data===null && $options===null && !$SERVER_DATA_OUTPUT) {
		PAGE(jscode("var SERVER_DATA = ".json_encode($SERVER_DATA).";"),"server_data");
		$SERVER_DATA_OUTPUT = true;
		return true;
	}
	
	$name = null;
	if (is_string($data)) {
		$name = $data;
		if ($options!==null) {
			$data = array($name=>$options);
		}
		
	}
	
	if ($data!==null) {
		$SERVER_DATA = array_merge($SERVER_DATA,$data);	
	}
	
	if ($name!==null) return $SERVER_DATA[$name];
	return true;
}


// added @ 13:56 24.12.2010.
function is_date_between($date,$date_start,$date_end) {
	$d = strtotime($date);
	$ds = strtotime($date_start);
	$de = strtotime($date_end);
	return ($ds <= $d && $d <= $de);
}

// added @ 18:53 17.1.2011.
function simpletext($x) {
	return htmlentities(trim(str_replace("\n"," ",strip_tags($x))));
}


// ENCRYPTION functions
define('CRYPT_KEY', '2904989330008');
function encrypt($text) {return trim(base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, CRYPT_KEY, $text, MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND))));}
function decrypt($text) {return trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, CRYPT_KEY, base64_decode($text), MCRYPT_MODE_ECB, mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB), MCRYPT_RAND)));} 



function validate_inputs($data,$validation) {
	$error = array();
	foreach ($validation as $entry=>$validator) {
		if (!preg_match($validator[0],$data[$entry])) {
			$error[$entry]=$validator[1];
		} else {
			$data[$entry]=trim(secure($data[$entry]));
			if ($validator[2] && count($functions=explode(",",trim($validator[2])))>0) {
				foreach ($functions as $adjust) {
					$data[$entry]=$adjust($data[$entry]);
				}
			}
		}
	}

	return array($data,$error);
}


// added 18:04 06.07.2011. // bimex
function days_in_month($year, $month) { 
	return date("t", strtotime($year . "-" . $month . "-01")); 
}

 
function date_range( $first, $last, $step = '+1 day', $format = 'Y-m-d' ) {

	$dates = array();
	$current = strtotime( $first );
	$last = strtotime( $last );

	while( $current <= $last ) {

		$dates[] = date( $format, $current );
		$current = strtotime( $step, $current );
	}

	return $dates;
}
 
 
function curdir($filename = null) {
	  $bt = debug_backtrace();
	  $dn = dirname($bt[0]["file"]);
	  if ($filename !== null) {
		$dn.="/".$filename;
	  }
	  return $dn;
}


function get_once($filename) {
	static $contents = array();
	if (isset($contents[$filename])) {
		return $contents[$filename];
	} else {
		return $contents[$filename] = file_get_contents($filename);
	}
}


function getDomain() {
	$s = empty($_SERVER["HTTPS"]) ? '' : ($_SERVER["HTTPS"] == "on") ? "s" : "";
    $sp = strtolower($_SERVER["SERVER_PROTOCOL"]);
    $protocol = substr($sp, 0, strpos($sp, "/")) . $s;
    $port = ($_SERVER["SERVER_PORT"] == "80") ? "" : (":".$_SERVER["SERVER_PORT"]);
    return $protocol . "://" . $_SERVER['HTTP_HOST'] . $port ;
}

function full_url()
{
    $s = empty($_SERVER["HTTPS"]) ? '' : ($_SERVER["HTTPS"] == "on") ? "s" : "";
    $sp = strtolower($_SERVER["SERVER_PROTOCOL"]);
    $protocol = substr($sp, 0, strpos($sp, "/")) . $s;
    $port = ($_SERVER["SERVER_PORT"] == "80") ? "" : (":".$_SERVER["SERVER_PORT"]);
    return $protocol . "://" . $_SERVER['HTTP_HOST'] . $port . $_SERVER['REQUEST_URI'];
}


function multi_implode($array, $glue = '') {
	if (!is_array($array)) return $array;
    $ret = '';

    foreach ($array as $item) {
        if (is_array($item)) {
            $ret .= multi_implode($item, $glue) . $glue;
        } else {
            $ret .= $item . $glue;
        }
    }

    $ret = substr($ret, 0, 0-strlen($glue));

    return $ret;
}

function xml_to_array( $xml ) {
	$xml = simplexml_load_string($xml);	
	
	$data = array();
	
	foreach ($xml as $name => $el) {
		$data[$name] =  str_replace("</{$name}>",'',str_replace("<{$name}>",'',$el->asXML())) ;
	}
	
	
	return $data;
	
	/*
	$pattern = "/<([^>]{1,})>([^<]{0,})<\/([^>]{1,})>/";
	if (preg_match_all($pattern,$xml,$matches)) {
		foreach ($matches[1] as $index => $tag ) {
			$out[$tag]=$matches[2][$index];
		}
		return $out; 
	} else {
		return false;
	}
	*/	
}

function array_to_xml( $data , $root = true ) {
	if (!is_array($data)) return $data;
	
	if ($root){
		$out = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<data>\n";
	}
	foreach ($data as $key => $value) {
		
		$inner_xml = array_to_xml($data , false);
		$out .= "<{$key}>{$inner_xml}</{$key}>\n";
		
	}
	if ($root){
		$out .="</data>\n";
	}
	
	return $out;

}

?>