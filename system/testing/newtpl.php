<? 
include_once("system.php");
$tpl = "<table border='1'>
	<thead>
		<tr>
			$([*.0]) {<th>[#]</th>}
		</tr>
	</thead>
	<tbody>
	$ {
		<tr>
			$ { <td>[*]</td> }
		</tr>
	}
	</tbody>
</table>";

$data = sql("select * from pages limit 100")->arr();

for ($j=0; $j<100; $j++) {
	foreach ($data as $i=>$r) {
		$nd[] = $r;
	}
}
$data = $nd;

function cons_var($var,$scope) {
	$var = trim($var);
	
	$varname = "";
	if ($var[0]=="'") {
		$varname = substr($var,0,strpos($var,"'",1)+1);
		$var = substr($var,strlen($varname));
		$index = 1;
	}
	
	$or_vector = explode("|",$var);
	$var = reset($or_vector);
	array_shift($or_vector);
	$or = array_pop($or_vector);
	
	$trans_vector = explode(":",$var);
	$var = array_shift($trans_vector);
	
	if ($var[0]=='@') {
		return substr($var,1);
	}
	$c = count($scope)-1;
	$var = explode(".",$var);
	$key_or_val = 0;
	
	
	
	
	foreach ($var as $key=>$part){
		
		switch($part) {
			case '*':
			break;
			case '**':
				$c--;
			break;
			case "#":
				$key_or_val = 1;
			break;
			case "#+":
				$key_or_val = 1;
				$prefix = "";
				$sufix = "+1";
			break;
			case "!#":
				$prefix = "count(";
				$sufix = ")-".$scope[$c][1]."-1";
				$c--;
			break;
			case "!#+":
				$prefix = "count(";
				$sufix = ")-".$scope[$c][1];
				$c--;
			break;
			case "~":
				$prefix = "count(";
				$sufix = ")";
			break;
			case "#%2":
				$prefix = "";
				$sufix ="%2";
				$key_or_val = 1;
			break;
			case "#+%2":
				$prefix = "(";
				$sufix ="+1)%2";
				$key_or_val = 1;
			break;
			
			default:
				//if ($index>0) {
					if ($part!='') $rest.="['{$part}']";
				/*} else {
					$varname = $part;
				}*/
			break;
		}
		$index++;
	}
	
	
	if ($var=='') {
		$varname = "";
	} else if ($varname=='') {
		$varname = $scope[$c][$key_or_val].$rest;
	}
	
	if ($or!='') {
		// escaping
		$or = str_replace("\\","\\\\",$or);
		$or = str_replace("'","\\'",$or);
		$prefix = "($varname == null) ? '{$or}' : ".$prefix;
	}
	
	$ctv = count($trans_vector);
	if ($ctv>0) {
		$prefix.= implode("(",array_reverse($trans_vector))."(";
		$sufix .=str_repeat(")",$ctv);
	}
	
	return $prefix.$varname.$sufix;
}

function compile($tpl,$pretty = false) {
	/*
	global $_compiled;
	if (isset($_compiled[$tpl])) return $_compiled[$tpl];
	*/
	
	$start = micronow();
	
	$allow_output = true;
	$loop_level = 0;
	$in_freetext = 1;
	$buffer="";
	$code = "\$x.='";
	
	$in_variable =0;
	$scope = array(array("\$data","\$key"));
	$scope_value = reset(end($scope));
	$len = strlen($tpl);
	if ($pretty) $n="\n";
	for ($i=0; $i<$len; $i++) {
		$c = $tpl[$i];
		$do_buffer = true;
		if ($pretty) {
			$t = str_repeat("\t",$loop_level);
			$tt = "$t\t";
		}
		
		switch ($c) {
			case '$':
				if (!$in_variable) {
					$scope_defined = false;
					if ($in_freetext) {
						$end_free_text = true;
						$in_freetext = false;
					}
					$expect_scope = true;
					$expect_loop = true; //echo "ex loop;";
					$do_buffer = false;
				}
			break;
			case '(':
				if ($expect_scope) {
					$expect_scope = false;
					$in_scope = true;
					$do_buffer = false;
				}
			break;
			case ')':
				if ($in_scope) {
					$in_scope = false;
					$expect_scope = false;
					$scope_defined = true;
					$scope_var = cons_var($variable,$scope);
					$buffer = "";
					$do_buffer = false;
				}
			break;
			case '[':
				if ($in_scope) {
					$in_scope_var = true;
					$do_buffer = false;
					$buffer="";
				} else if ($expect_scope) {
					$var_buffer = "";
					$in_delimiter = true;
					$do_buffer = false;
					$in_freetext = false;
					$allow_output = false;
				} else {
					$in_variable  = true;
					if ($in_freetext) {
						$code.=$buffer."';$n";
						$buffer="";
					}
					$in_freetext = false;
					$allow_output = false;
					$var_buffer = "";
					$do_buffer = false;
				}
			break;
			case ']':
				if ($in_scope_var) {
					$in_scope_var = false;
					$variable = $buffer;
					$buffer = "";
					$do_buffer = false;
				} else if ($in_delimiter) {
					$scope_delimiter[$loop_level] = $var_buffer;
					$do_buffer = false;
					$in_delimiter = false;
					$allow_output = true;
				} else if ($in_variable) {
					$allow_output = true;
					$variable = cons_var($var_buffer,$scope);
					$code.="{$t}\$x.={$variable};$n";
					$in_freetext = true;
					$code .= "{$t}\$x.='";
					$in_variable = false;
					$do_buffer = false;
				}
			break;
			case '{':
				if (!$scope_defined) {
					$scope_var = reset(end($scope));
					$scope_defined = true;
					$expect_scope = false;
				}
				if ($expect_loop) {
					$parent_scope_value = $scope_var;
					$in_freetext = true;
					$expect_loop = false;
					$scope_key = "\$k".$loop_level;
					$scope_value = "\$v".$loop_level;
					//$vr = var_export($scope,true);
					$scope[] = array($scope_value,$scope_key);
					if ($scope_delimiter[$loop_level]!='') {
						$counter_var = "\$i{$loop_level}";
						$delimiter = str_replace("'","\\'",str_replace("\\","\\\\",$scope_delimiter[$loop_level]));
						$counter_code = "$counter_var=count($parent_scope_value);{$n}";
						$scope_delimiter[$loop_level] = "{$t}if (--{$counter_var} > 0) \$x.='{$delimiter}';{$n}{$tt}";
						$delimiter = "";
					}
					$code.= "{$t}if (is_array($parent_scope_value)) {{$n}{$t}{$counter_code}{$t}foreach ($parent_scope_value as $scope_key => $scope_value) {{$vr}{$n}{$tt}\$x.='";
					$counter_code = "";
					$in_loop = true;
					$loop_level++;
					$do_buffer = false;
				}
			
			break;
			case '}':
				if ($loop_level>0) {
					if ($in_freetext) {
						$code.=$buffer."';{$n}";
						$buffer="";
					}
					
					$loop_level--;
				
					$code.="{$n}{$t}{$scope_delimiter[$loop_level]}}{$n}{$t}}{$n}\$x.='";
						$t = substr($t,0,strlen($t)-1);
					$delimiter_check = "";
					$counter_code = "";
					$do_buffer = false;
					
					array_pop($scope);
				}
			
			break;
			case "'":
			case '\\':
				if ($in_freetext) {
					$c = "\\$c";
				}
			break;
			default:
				if ($in_freetext) {
					$code.="";
				}
			break;
		}
		
		if ($do_buffer) {
			if ($allow_output) $buffer.=$c;
			$var_buffer.=$c;
		}
			
		if ($end_free_text) {
			$code.=$buffer."';{$n}";
			$end_free_text = false;
			$buffer="";
		}
		
	}
	
	if ($in_freetext) {
		$code .= "{$buffer}';{$n}";
	}
	
	// echo "Compile time:".microdiff($start)."\n";
	return $code;
	
}

function newproduce($tpl,$data,$key=null) {	
	eval(compile($tpl));
	return $x;
}

/*

$s = micronow();
$out = produce($tpl,$data);
$e1 = microdiff($s);
echo $e1."\n";


$s = micronow();
$out = newproduce($tpl,$data);
$e2 = microdiff($s);
echo $e2."\n";

echo ($e1/$e2)."x brze\n";

echo newproduce("[shit|none]",array("shit"=>"some stuff"));


$tpl = "
[@__URLPATH__]
['d.m.Y. H:i:s':date]
$[----------------------]{
([~]) :[#+%2]:[#%2]: [!#] [id] [name]
$[|]([ch]){ <[#]> [*]  }
}
";

$data = array(
	array("id"=>"1","name"=>"Kristijan",'ch'=>array(1,2,3)),
	array("id"=>"2","name"=>"Konrad",'ch'=>array(4,5,6)),
	array("id"=>"2","name"=>"Konrad",'ch'=>array(4,5,6)),
	array("id"=>"2","name"=>"Konrad",'ch'=>array(4,5,6)),
	array("id"=>"2","name"=>"Konrad"),
);

echo compile($tpl,true);
echo newproduce($tpl,$data);
*/

$s = micronow();
$out  = newproduce(TPL_STD_TABLE,$data);
$e1 = microdiff($s);
echo $e1."\n";


$s = micronow();
$out  = produce(TPL_STD_TABLE,$data);
$e2 = microdiff($s);
echo $e2."\n";
echo $e2/$e1." times faster!\n";


$s = micronow();
$out  = produce(TPL_STD_TABLE,$data);
$e2 = microdiff($s);
echo $e2."\n";
?>