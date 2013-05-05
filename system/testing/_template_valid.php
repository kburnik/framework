<?

class _template {
	var $cases = array();
	function get_variable_value($var_sufix,$scope,$is_null) {
		$value="";
		eval("\$value = \$scope{$var_sufix};");
		$is_null = ($value===null) ;
		return $value;
	}
	function construct_variable($variable) {
		return  "['".str_replace(".","']['",$variable)."']";
		/*
		$variable = explode(".",$variable);
		$variable = implode("']['",$variable);
		*/
		// $variable ="['{$variable}']";
		return $variable;
	}

	function produce($tpl,$data,$key=null,$parent_scope=null,$global_counter=0,$delimiter='') {
		// echo $tpl."<br/>";
		$expect_loop_scope = false;
		$in_loop_scope = false;
		$in_loop_block = false;
		$in_simple_variable = false;
		$paren_level = 0;
		$block_level = 0;
		$is_null = false;
		$bracket_level = 0;
		$out = "";
		
	 	$scope = array(
			">>"=>"",
			"<<"=>""
		);
		
		$count = count($data);
		
		$scope = array_merge(array(
			"**"=>$parent_scope,
			"*"=>$data,
			"##"=>$global_counter,
			"#"=>$key,
			"#+"=>$key+1,
			"!#"=>$count-$key-1,
			"!#+"=>$count-$key,
			"~"=>$count,
			"#%2"=>$key%2,
			"#+%2"=>($key+1)%2
		),$scope);
				
		if (is_assoc($data)) {
			$scope = array_merge($scope,$data);
		}
		
		$buffer = "";
		$allow_output = true;
		$expect_simple_variable  = true; // assume simplest template :: variables in form {field} ,{a}, {*}, etc.
		
		$len = strlen($tpl);
		for ($i=0; $i < $len; $i++) {
			$c = $tpl[$i];
			$def_case = false;
			switch ($c) {
				case "[":
					$bracket_level++;
					if ($in_loop_scope) {
						if ($expect_loop_scope_param==1) {
							$buffer="";
						} else {
							$buffer.=$c;
						}
					} else {
						if ($expect_simple_variable) {
							$in_simple_variable = true;
							$expect_simple_variable = false;					
							$allow_output = false;
							$buffer = "";
						} else if ($expect_loop_block) {
							$buffer = "";
							$in_delimiter = true;
						} else {
							$buffer.=$c;
						}
					}
				break;
				case "]":
					$bracket_level--;
					if ($in_loop_scope) {
						if ($expect_loop_scope_param==1) {
							$expect_loop_scope_param=2;						
							$variable = $this->construct_variable($buffer);
							$buffer="";
						}
					} else if ($in_simple_variable) {
							// assume null
							$is_null = true;
							
							if ($simple_var_has_alternate_definition) {
								$alt_definition  = substr($buffer,$var_alt_def_start+1);
								$buffer = substr($buffer,0,$var_alt_def_start);
							}
							
							if ($variable_transformation) {
								$variable_transformation = false;
								$trans_vector = explode(":",substr($buffer,$var_trans_start));
								$buffer = substr($buffer,0,$var_trans_start);
								array_shift($trans_vector);
							}
							
							$in_simple_variable = false;
							$expect_simple_variable = true;
							
							$variable = $this->construct_variable($buffer);
							
							if ($simple_var_quotes_present) {
								$value = $buffer;
								$simple_var_in_single_quote = false;
								$simple_var_quotes_present = false;
								$is_null = false;
							} else {
								// is variable a constant (e.g. @MYSQL_ASSCO)
								if ($simple_var_is_const) {
									$buffer = substr($buffer,1);
									if (defined($buffer)) {
										$value = constant($buffer);
										$is_null = false;
									} else {
										$value = "";
										$is_null = true;
									}
									$simple_var_is_const = false;
								} else {
									$value = $this->get_variable_value($variable,$scope,&$is_null);
								}
							}
							
							
							// run given tranformations on variable value
							if (count($trans_vector)>0) {
								// echo "runing func vector:".implode(",",$trans_vector)."on $value\n";
								$value = run_function_vector($trans_vector,$value);
								$trans_vector = array();
							}
							
							if ($simple_var_has_alternate_definition && $value=='') {
								// alternate definitions: e.g [*|Nema podataka]
								$simple_var_has_alternate_definition = false;
								$out .= $alt_definition;
							} else {								
								if (!$is_null) {
									$out .= $value;
								} else {
									// just copy the expression
									$out .= "[$buffer]";
								}
							}
							$allow_output = true;
							$buffer = "";
					} else if ($in_delimiter) {
						$delimiter = $buffer;
						$buffer = "";
						$in_delimiter = false;
					} else {
						$buffer.=$c;
					}
					
					
				break;		
				case "{":
					$block_level++;
					
					if ($expect_loop_scope) { // this occurs when running into $ { } // then transform it to $({0}) { } 
						// loop scope is assumed to be default
						$expect_loop_scope = false;
						$in_loop_scope = false;
						$scope_defined = true;
						$expect_loop_scope_param = 0;
						
						// now gather the loop block
						$allow_output = false;
						$expect_loop_block = true; // already set at "$" case
						
						$buffer="";
						$variable ="['*']";
					}

					if ($expect_loop_block) {
						$expect_loop_scope = false;
						$buffer = "";
						$expect_loop_block = false;
						$in_loop_block = true;
						$expect_simple_variable=false;
					} else {
						$buffer.=$c;
					}
				
				break;
				case "}":
					$block_level--;
					
					if ($in_loop_block) {
						if ($block_level==0) {
							// the whole block has been gathered;
							$in_loop_block = false;
							$expect_loop_block = false;
							$block = $buffer;
							$value = $this->get_variable_value($variable,$scope,&$is_null);
							if (!$is_null) {
								// this will only work for simple loop definitions like $({abc}) {}   or   $({*}) {}
								$item_index = 0;
								$item_last_index = count($value)-1;
								foreach ($value as $item_key=>$item_value) {
									if ( ++$item_index > $item_last_index) $delimiter = '';
									$out .= $this->produce($block,$item_value,$item_key,$scope,&$global_counter).$delimiter;
									$global_counter++;
								}
								$delimiter = '';
							} else {
								$out .= "{{$block}}";
							}
							$expect_simple_variable = true;
							$allow_output = true;
							$buffer = "";
						} else {
							$buffer.=$c;
						}
					} else {					
						$buffer.=$c;
					}
				
				break;
				case "$":
					if (!$in_loop_block) {
						$expect_loop_scope = true;
						$scope_defined = true; // assume only
						$expect_loop_block = true;
						$expect_simple_variable = false;
						$allow_output = false;
					} else {
						$buffer.=$c;
					}
				break;
				case "(":
					if ($expect_loop_scope) {
						$paren_level++;
						$in_loop_scope = true;
						$scope_defined = false;
						$allow_output  = false;
						$expect_loop_scope = false;
						$expect_loop_scope_param = 1; // first param
						$buffer="";
					} else {
						$buffer.=$c;
						if (!$in_loop_block && !$in_simple_variable) {
							$out.=$c;
						}
					}
				break;
				case ")":
					if ($in_loop_scope) {
						$paren_level--;
						if ($paren_level==0) {
							$in_loop_scope = false;
							$scope_defined = true;
							$expect_loop_scope_param = 0;

							$allow_output = false;
							$expect_loop_block = true; // already set at "$" case
							
							$buffer="";
						}
					} else {
						$buffer .= $c;
						if (!$in_loop_block && !$in_simple_variable) {
							$out.=$c;
						}

					}
				break;
				case ":":
					if ($in_simple_variable && !$simple_var_in_single_quote && !$variable_transformation) {
						$variable_transformation = true;
						$var_trans_start = strlen($buffer);
					}
					$buffer.=$c;
				break;
				case "'":
					if ($in_simple_variable) {
						$simple_var_in_single_quote = !$simple_var_in_single_quote;
						$simple_var_quotes_present = !$simple_var_in_single_quote;
					} else {
						$buffer.=$c;
					}
				break;
				case "|":
					if ($in_simple_variable) {
						if (!$simple_var_has_alternate_definition) {
							$simple_var_has_alternate_definition =  !$simple_var_in_single_quote;
							$var_alt_def_start = strlen($buffer);
						}
					}
					$buffer.=$c;
				break;
				case "@":
					if ($in_simple_variable) $simple_var_is_const = true;
					$buffer .= $c;
				break;
				case "\\":
					$c = $tpl[$i+1];
					if ($allow_output) $out .= $c;
					$buffer .= "\\$c";
					$i++;
				break;
				default:
					$def_case = true;
					if ($allow_output) $out .= $c;
					$buffer.=$c;
				break;
			}
			if (!$def_case) $this->cases[$c]++;
		}

		return $out;
	}
	
	function __destruct() {
		//echo "Destroying template<br />";
	}
}

function produce($tpl,$data,$key=null,$parent_scope=null,$global_counter=0) {
	global $_template;
	return $_template->produce($tpl,$data,$key=null,$parent_scope=null,$global_counter=0);
}

$_template = new _template();

?>