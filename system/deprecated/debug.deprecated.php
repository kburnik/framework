<?
class _php_struct_view {
	var $INSTANCE_ID=0;
	
	var $js ='
	<script type="text/javascript" src="http://code.jquery.com/jquery-1.4.3.min.js"></script>
	<script type="text/javascript">
		var DEBUG__LOADER = function() {
			if (typeof $ == "undefined") {				
				document.write("PHP_STRUCT_VIEW: jQuery not present in script!");
			} else {
				$(function() {
					var animDuration = 300;
					$(".php.expander").click(function(){
						var id = $(this).attr("rel");
						var $el = $(id);
						if ($el.hasClass("expanded")) {
							$el.slideUp(animDuration).removeClass("expanded");
							$(this).html("+");
						} else {
							$el.show(animDuration).addClass("expanded");
							$(this).html("-");
						}
					});
				});
			}
		}
		DEBUG__LOADER();
	</script>
	';
	
	var $css='<style type="text/css">
	.php {font:11px Calibri;}
	
	.php.array {
		display:none;
		background:#f4f4f4;
		margin-bottom:3px;
		/*display:inline-block;*/
	}
	.php.array.expanded {
		display:block;
	}

	.php.expander {	
		display:block;
		/*display:inline-block;*/
		cursor:pointer;
		padding:1px;
		width:12px;
		height:12px;
		margin-left:3px;
		text-align:Center;
		border:1px solid black;
	}

	.php.key {

		background:#ddddff;
		color:#555555;
		padding:2px;
		margin:2px;
		margin-left:0px;
		/*display:inline-block;*/
		border:1px solid #aaaaaa;
	}

	.php.value {
	font-weight:bold;
	background:white;
	padding:2px;
	margin:2px;
	margin-left:0px;
	/*display:inline-block;*/
	border:1px solid #aaaaaa;	
	}

	.php.atom {
	background:#fafafa;
	}

	.php.pair {
	/*display:block;*/
	border-bottom:3px solid #dddddd;
	margin-bottom:2px;
	}
	</style>';
	
	function show($data,$key="Output",$tabs=0,$level=0) {
		$t = str_repeat("\t",$tabs);
		$tt = $t."\t";
		
		if ($this->INSTANCE_ID==0) {
			$prefix = $this->css.$this->js;
		}
		
		$expander_symbol = "+";
		if ($level==0) {$prefix.="<table class='php'>"; $sufix = "</table>"; }
		if ($level<=1) {
			$expanded="expanded";
			$expander_symbol='-';	
		}
		
		$this->INSTANCE_ID++;
		$id = "php-array-{$this->INSTANCE_ID}";
		if (is_array($data)) {
			
			foreach ($data as $k=>$value) {
				$values .= "\n{$tt}".show_struct($value,$k,$tabs+1,$level+1);
			}
			return "
				{$prefix}
					<tr class='php pair'>
						<td valign='top'><a class='php expander' rel='#{$id}'>{$expander_symbol}</a></td>
						<td class='php key' valign='top'>
							<label>{$key}</label>
						</td>
						<td>
							<table id='{$id}' class='php array {$expanded}'>{$values}</table>
						</td>
					</tr>
				{$sufix}
				";
		
		} else {
				if (is_object($data)) $data = var_export($data,true);
				return "{$prefix}<tr class='php pair'><td valign='top'>&nbsp;</td><td class='php key' valign='top'><label>{$key}</label></td><td class='php value'>{$data}</td></tr>{$sufix}";
			
		}
	}
}

function show_struct($data,$key="Output",$tabs=0,$level=0) {
	global $_php_struct_view;
	if (!isset($_php_struct_view)) {
		$_php_struct_view = new _php_struct_view();
	}
	return $_php_struct_view->show($data,$key,$tabs,$level);
}

function ondebug($func) {
	if (isset($_GET['debug'])) $func();
}

function writelog($data,$mode="a") {
	if (!in_array($mode,array("a","w"))) $mode = "a";
	$d = fopen(__LOGFILE__,$mode);
	fwrite($d,date("[Y-m-d H:i:s] "));
	if (!is_string($data)) $data = var_export($data,true);
	fwrite($d,$data."\n");
	fclose($d);
}

?>