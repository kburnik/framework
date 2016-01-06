<?php

include_once(dirname(__FILE__)."/common_templates.php");

// TODO(kburnik): This should be deprecated as soon as possible.
// The new template implementations is in now in Tpl class.
class _template {

  function __construct() {}

  function __destruct() {}

  private function cons_var($var, $scope) {
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

    $trans_vector =
      explode(":", str_replace('::', '<?/*DOUBLE_SEMICOLON*/?>', $var));
    $var = array_shift($trans_vector);

    if ($var[0]=='@')
      return "constant('" . substr($var, 1) . "')";

    $c = count($scope) - 1;
    $var = explode(".", $var);
    $key_or_val = 0;

    foreach ($var as $key => $part) {
      switch($part) {
        case '*': // current context value operator
          break;
        case '**': // parent context value operator
          $c--;
          break;
        case "#": // current context key
          $key_or_val = 1;
          break;
        case "#+": // current context key + 1
          $key_or_val = 1;
          $prefix = "";
          $sufix = "+1";
          break;
        case "!#": // current context reverse order key
          $prefix = "count(";
          $sufix = ")-".$scope[$c][1]."-1";
          $c--;
          break;
        case "!#+": // current context reverse order key + 1
          $prefix = "count(";
          $sufix = ")-".$scope[$c][1];
          $c--;
          break;
        case "~": // number of elements (count)
          $prefix = "count(";
          $sufix = ")";
        break;
        case "#%2": // index mod 2 operator
          $prefix = "";
          $sufix ="%2";
          $key_or_val = 1;
          break;
        case "#+%2": // index+1 mod 2 operator
          $prefix = "(";
          $sufix ="+1)%2";
          $key_or_val = 1;
          break;
        case "#last": // output last if last element, otherwise output middle
          $prefix = "((end(array_keys(".$scope[$c-1][0].")) == ";
          $sufix = ") ? 'last' : 'middle' )";
          $key_or_val = 1;
          break;
        default:
          if ($part!='') $rest.="['{$part}']";
         break;
      }
      $index++;
    }

    if ($var=='') {
      $varname = "";
    } else if ($varname == '') {
      $varname = $scope[$c][$key_or_val] . $rest;
    }

    if ($or != '') {
      // escaping
      $or = str_replace("\\" , "\\\\", $or);
      $or = str_replace("'" , "\\'", $or);
      $prefix = "($varname == null) ? '{$or}' : " . $prefix;
    }

    $ctv = count($trans_vector);
    if ($ctv > 0) {
      $prefix .= str_replace('<?/*DOUBLE_SEMICOLON*/?>',
                             '::',
                             implode("(", array_reverse($trans_vector)) . "(");
      $sufix .= str_repeat(")", $ctv);
    }

    return $prefix . $varname . $sufix;
  }

  function compile($tpl, $pretty = false, $class = false) {
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

    if ($pretty)
      $n = "\n";

    for ($i=0; $i<$len; $i++) {
      if ($escaped>0)
        $escaped--;

      $c = $tpl[$i];
      $do_buffer = true;
      $skip_do_buffer = false;

      if ($pretty) {
        $t = str_repeat("\t", $loop_level);
        $tt = "$t\t";
      }

      switch ($c) {
        case '$':
          if ($escaped) {
            $do_buffer = true;
            $escaped = 0;
          } else if (!$in_variable) {
            $scope_defined = false;
            if ($in_freetext) {
              $end_free_text = true;
              $in_freetext = false;
            }
            $expect_scope = true;
            $expect_loop = true;
            $expect_comment = true;
            $do_buffer = false;
          }
          break;
        case '?':
          if ($expect_scope) {
            $expect_comment = false;
            $expect_loop = false;
            $expect_scope = false;
            $do_buffer = false;
            $expect_condition = true;
          }
          break;
        case '(':
          if ($expect_comment) {
            $expect_comment = false;
          }

          if ($expect_scope) {
            $expect_scope = false;
            $in_scope = true;
            $do_buffer = false;
          } else if ($expect_condition) {
            $expect_condition = false;
            $in_condition = true;
            $buffer = "";
            $do_buffer = false;
          }
          break;
        case ')':
          if ($in_scope) {
            $in_scope = false;
            $expect_scope = false;
            $scope_defined = true;
            $scope_var = $this->cons_var($variable,$scope);
            $buffer = "";
            $do_buffer = false;
          } else if ($in_condition) {
            $in_condition = false;
            $expect_truth_block = true;
            $code .= 'if (' . $buffer . ' ) {  $x.=';
            $buffer = "";
            $do_buffer = false;
          }
          break;
        case '[':
          if ($expect_comment) {
            $expect_comment = false;
          }

          if ($escaped) {
            $do_buffer = true;
            $escaped = 0;
          } else if ($in_scope) {
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
          if ($escaped) {
            $do_buffer = true;
            $escaped = 0;
          } else if ($in_scope_var) {
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
            $variable = $this->cons_var($var_buffer,$scope);

            if (!$in_condition) {
              $code.="{$t}\$x.={$variable};$n";
              $in_freetext = true;
              $code .= "{$t}\$x.='";
            } else {
              // in condition
              $buffer .= "{$variable}";
            }

            $in_variable = false;
            $do_buffer = false;
          }
          break;
        case '{':
          if ($expect_comment) {
            $expect_comment = false;
          }
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
          } else if ($expect_truth_block) {
            $parent_scope_value = $scope_var;
            $in_freetext = true;
            $expect_truth_block = false;
            $code .=  " '";
            $do_buffer = false;
            $truth_block_level++;
          } else if ($expect_lie_block) {
            $expect_lie_block = false;
            $code = substr($code,0,strlen($code)-strlen("\$x .= '"));
            $code .= " else { \$x .= '";
            $buffer="";
            $in_freetext = true;
            $do_buffer = false;
            $truth_block_level++;
          }

          break;
        case '}':
          if ($truth_block_level>0) {
            $truth_block_level --;
            $code .= $buffer."'; } "."\$x .= '";
            $buffer = "";
            $in_freetext = true;
            $do_buffer = false;
            $expect_lie_block = true;
          } else if ($loop_level>0) {
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
          if ($in_freetext) {
            $c = "\'";
            $do_buffer = true;
          }
          break;
        case '\\':
          $escaped=2;
          $do_buffer = false;
          break;
        case '/':
          if ($expect_comment) {
            $expect_comment_start = true;
            $expect_comment = false;
            $allow_output = $do_buffer = false;
          } else if ($expect_comment_final) {
            $expect_comment_final = false;
            // free up the buffer
            $code.=$buffer.$c;
            $buffer="";

            continue;
          }
          break;
        case '*':
          if ($expect_comment_start) {
            $buffer.="/";
            $expect_comment_end = true;
            $allow_output = $do_buffer = true;
          } else if ($expect_comment_end) {
            $expect_comment_final = true;
            $do_buffer = true;
          }
          break;
        default:
          $expect_comment_start = false;
          if ($in_freetext) {
            $code .= "";
          }
      }

      if ($do_buffer) {
        if ($allow_output) $buffer.=$c;
        $var_buffer.=$c;
      }

      if ($end_free_text) {
        $code .= $buffer . "';{$n}";
        $end_free_text = false;
        $buffer = "";
      }

      if ($in_condition) {
        $do_buffer = true;
      }

    }

    if ($in_freetext && !$expect_comment_final) {
      $code .= "{$buffer}';{$n}";
    }

    $this->worktimes[] = microdiff($start);

    return $code;
  }

  public function produce($tpl, $data, $use_cache = true, $ignored_param = false) {
    $s = micronow();
    $tpl_function = "tpl_".md5($tpl);
    if ($use_cache) {
      $tpl_file = Project::GetProjectDir("/gen/template/" .
                                         $tpl_function . ".php");
    }

    // check if template is already compiled
    if ($use_cache && function_exists($tpl_function)) {
      // hit ... We're rollin
      $x = $tpl_function($data);
    } else if ($use_cache && file_exists($tpl_file) ) {
      // minor penalty ... load the file and return result
      include_once($tpl_file);
      $x = $tpl_function($data);
    } else {
      // penalty .... now we have to compile and store the function...
      $code = $this->compile($tpl, false);
      eval($code);
      if ($use_cache) {
        $function = "\n\n function {$tpl_function}(\$data=array()) { $code \n return \$x; }";
        file_put_contents($tpl_file,"<? {$function} ?>");
      }
    }

    return $x;
  }

}

function tpl() {
  static $_template;

  if (!isset($_template)) {
    if (defined('PROJECT_USE_LEGACY_TEMPLATE') &&
        constant('PROJECT_USE_LEGACY_TEMPLATE')) {
      $_template = new _template();
    } else {
      $_template = new Tpl(false, false);
    }
  }

  return $_template;
}

function produce($tpl, $data = array(), $use_cache=true, $do_warn=true) {
  return tpl()->produce($tpl, $data, $use_cache, $do_warn);
}

function produceview($filename,$data) {
  $view = produce(get_once($filename), $data);

  return $view;
}
