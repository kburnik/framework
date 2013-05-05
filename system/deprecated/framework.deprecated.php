<?

class BASE_FRAMEWORK {
	
	function __construct() {	
		return $this;
	}
	
	public function worktime($reset = false) {
		if ($reset) {
			$this->main->start_time = micronow(); 
		}
		return $this->main->worktime;
	}
	
	function push($value,$varname=null,$append = false) {
		$this->value = $value;

		if ($varname!==null)  {
			if ($append) {
				$this->__appendto($varname);
			} else {
				$this->main->vars[$varname] = $this->value;
			}
		}
		return $this->value;
	}
	
	
	private function __appendto($varname) {
		if (is_array($this->main->vars[$varname])) {
			$this->main->vars[$varname][] = $this->value;;
		} else {
			$this->main->vars[$varname] .= $this->value;;
		}
	}
	function appendto($varname) {
		$this->__appendto($varname);
		return  $this->main->vars[$varname];
	}
	
	function ato($varname) {
		$this->__appendto($varname);
		return  $this->main->vars[$varname];
	}
	
	private function __prependto($varname) {
		if (is_array($this->main->vars[$varname])) {
			array_unshift($this->main->vars[$varname],$this->value);
		} else {
			$this->main->vars[$varname] = $this->value . $this->main->vars[$varname];
		}
		
	}
	function prependto($varname) {
		$this->__prependto($varname);
		return $this->main->vars[$varname];
	}
	
	
	function append($new_value) {
		$value = $this->value;
		if (is_array($value)) {
			$value[] = $new_value;
		} else {
			$value .= $new_value;
		}
		return $value;
	}
	
	function prepend($new_value) {
		$value = $this->value;
		if ( is_array($value) ) {
			array_unshift( $value, $new_value );
		} else {
			$value = $new_value . $value ;
		}
		return $value;
	}
	
	function with($varname) {
		$args = func_get_args();
		if (count($args)==1) {
			return $this->main->vars[ $varname ];
		} else {
			$list = array();
			foreach ($args as $varname) {
				$list[ $varname ] = $this->with($varname);
			}
			return $list;
		}
		return $this->value;
	}
	
	function to($varname) {
		$args = func_get_args();
		if (count($args)==1) {
			if (substr($varname,-2)=="[]") {
				$this->main->vars[substr($varname,0,strlen($varname)-2)][] = $this->value;
			} else {
				$this->main->vars[$varname] = $this->value;
			}
		} else {
			$values = $this->value;
			foreach ($args as $varname) {
				$this->value = current($values);
				$this->to($varname);
				next($values);
			}
			$this->value = $values;
		}
		
		return $this->value;
	}
	
	function clear() {
		if (count ($args = func_get_args()) > 0) {
			foreach ($args as $var) {
				unset($this->main->vars[$var]);
			}
		} else {
			$this->main->vars = array();
		}
		return $this->value = null;
	}
	
	function show($varname=null,$func="print_r") {
		$out = ($varname===null) ? $this->value : $this->main->vars[$varname] ;
		print_r($out);
		return $out;
	}
	
	function output() {
		return $this->main->vars;
	}
	
	function p($what) {		
		print_r( $what );
	}
	
	function run($filename) {
		return include($filename);
	}
	
}



class SQL_FRAMEWORK extends BASE_FRAMEWORK {
	var $query = array();
	var $resource = array();
	var $sql = null;
	
	// private part
	private function sql($query) {
		return sql($query);
	}
	
	// public part
	function sqlcell($query) {
		return $this->sql($query)->cell();
	}
	
	function sqlarr($query,$transformations='') {	 // implement transformations
		return $this->sql($query)->arr();
	}
	
	function sqlvector($query) {
		return $this->sql($query)->vector();
	}
	
	function sqlbinmap($query) {
		return $this->sql($query)->binmap();
	}
	
	function sqlmap($query) {
		return $this->sql($query)->map();
	}
	
	
	
	function sqlproduce($template="\${[*]}",$varname=null) {
		$out =  produce ( $template, $this->sql($this->value)->arr() );
		if ($varname!=null) $this->main->vars[ $varname ]  = $out;
		return $out;
	}
	
}

class ARRAY_FRAMEWORK extends SQL_FRAMEWORK {
	/**	Map an array by a field value in a subarray; Field should be unique!
	 * @param string $field ; Field to map by
	 * @return array ; mapped array by selected $field
	 **/
	function mapby($field) {
		foreach ($this->value as $key=>$val) {
			$out[ $val[$field] ] = $val;
		}
		return $out;
	}
	
	public function produce($template="$ {[*]}",$varname=null) {
		//if (is_string($value)) $value = sqlarr($value);
		$out = produce ( $template, $this->value ) ;
		// if ($varname!=null) $this->main->vars[ $varname ] = $out;
		return $out;
	}
	
	function count() {
		return count ( $this->value ) ;
	}
	
	function first() {
		return  reset($this->value) ;
	}
	
	function last() {
		return end($this->value) ;
	}
	
	function item($index) {
		return $this->value[$index];
	}
	
	function keys() {
		return array_keys($this->value) ;
	}
	
	function merge() {
		return call_user_func_array("array_merge",$this->main->vars) ;
	}
	
	function mergeto($varname) {
		return $this->main->vars[$varname] = array_merge ($this->main->vars[$varname],$this->value);
	}
	
	function extract() {
		$this->main->vars = array_merge( $this->main->vars, $this->value);
	} 
	
	/** 
	 * Returns an array with elements picked out by given $keys 
	 * {@source }
	 * @param string $key,...  
	 * @return array
	 **/
	
	function array_pick() {
		$args = func_get_args();
		$out = array();
		foreach ($args as $varname) {
			$out[ $varname ] = $this->value[ $varname ];
		}
		return $out;
	}
	
	
	// array conversion
	
	function tocolumn($colname) {
		$table = array();
		foreach ($this->value as $cell) {
			$table[] = array( $colname => $cell);
		} 
		$this->main->vars[ $colname] = $table;
		return $table;
	}
	
	
	function mergecolumns() {
		$args = func_get_args();
		if (count($args)==0) $args = array_keys($this->main->vars);
		$table = array();
		if (count($args)>0 && isset($this->main->vars[ $args[0] ])) {
			$table = $this->main->vars[ $args[0] ];
			array_shift($args);
			foreach ($table as $i=>$c) {
				foreach ($args as $tblname) {
					$row = $this->main->vars[$tblname][$i];
					$table[$i] = array_merge($table[$i],$row);
				}
			}
		}
		return $table;
	}
}

class OUTPUT_FRAMEWORK extends ARRAY_FRAMEWORK {
	function json($pretty = false){
		$value = json_encode($this->value);
		if ($pretty) $value = json_format($value);
		return $value;
	}
	
	function implode($sep='') {
		return implode ( $sep, $this->value ) ;
	}
	
	function explode($sep) {
		return explode( $sep, $this->value );
	}
	
	function replace($search,$replace) {
		return str_replace( $search, $replace, $this->value);
	}
	function wrap($prefix,$sufix) {
		return $prefix . $this->value . $sufix ;
	}
}


class DATETIME_FRAMEWORK extends OUTPUT_FRAMEWORK {

	function todate($format = TPL_STD_DATETIME ) {
		return date($format, $this->value);
	}
	
}

class FILESYSTEM_FRAMEWORK extends DATETIME_FRAMEWORK {
	
	private $current_directory  = ".";
	// file contents

	function fget($filename) {
		return file_get_contents($filename) ;
	}
	
	function fput($filename) {
		file_put_contents($filename, $this->value );
		return $this->value;
	}
	
	function fclear($filename) {
		file_put_contents($filename, "" );
		return $this->value;
	}
	
	
	function fappend($filename) {
		$f= fopen($filename,"a");
			fwrite($f, $this->value );
		fclose($f);
		
		return $this->value;
	}
	
	
	/// directories and files
	
	public function dir($root=null) {
		if ($root==null) $root= dirname( __FILE__ );
		
		// adjust root
		$root=trim($root);
		if (!in_array(substr($root,-1),array('/','\\'))) $root.="/";
		$root = str_replace("\\","/",$root);
		
		$this->current_directory = $root;
		
		$d = dir($root);
		while (false !== ($entry = $d->read()))  $directory_list[]=$entry;
		return $directory_list;
	}
	
	private function is_matching($filename,$filters) {
		$is_matching=false;
		$filters = explode(",",$filters);
		
		if (count($filters)==0) return false;
		
		foreach ($filters as $filter) {
			
			$replaces=array(
				"." => "[\\.]",
				"*" => "(.*)"
			);
			
			foreach ($replaces as $match=>$replace) {
				$filter=str_replace($match,$replace,$filter);
			}
			$filter="/$filter/";
			
			$match = preg_match($filter,$filename);
			
			if ($match) {
				$is_matching = true;
				break;
			}
		
		}
		
		return $is_matching;
	}
	
	private function is_relativedir($dirname) {
		return in_array($dirname,array(".",".."));
	}
	
	public function getfiles($filters='',$fullpath=false) {
		$dir = $this->value;
		$out=array();
		foreach ($dir as $item ) {
			if (is_file( $this->current_directory . $item )) {
				if ($this->is_matching($item,$filters)) {
					if ($fullpath) $item = $this->current_directory . $item;
					$out[]=$item;
				}
			}
		}
		return $out;
	}
	
	public function getdirs($filters='',$fullpath=false,$use_relative_dirs=false) {
		$dir = $this->value;
		$out=array();
		foreach ($dir as $item ) {
			if (
				is_dir( $this->current_directory . $item ) && 
				$this->is_matching($item,$filters) &&
				( $use_relative_dirs >= $this->is_relativedir($item) ) // NOTE: using >= with logical operators
			) {
				if ($fullpath) $item = $this->current_directory . $item;
				$item = str_replace("\\","/",$item);
				$out[]=$item;
				
			}
		}
		return $out;
	}
	
	public function pretty_filesize() {
		return $this->value;
		$size = $this->value;
		$measures = explode(",","B,KiB,MiB,GiB,TiB");
		$i=0;
		while ($size > 1024) {
			$size /= 1024;
			$i++;
		};
		$size = round($size,2)." ".$measures[$i];
		return $size;
	}
	
	
}

class WEB_FRAMEWORK extends FILESYSTEM_FRAMEWORK {
	
	function js() {
		$tabs = str_repeat("\t",2);
		$args = func_get_args();
		return produce ( "$ {{$tabs}" . TPL_JS . "\n}" , $args ) ;
	}
	
	function css() {
		$tabs = str_repeat("\t",2);
		$args = func_get_args();
		return produce ( "$ {{$tabs}" . TPL_CSS . "\n}" , $args ) ;
	}
	
	function comment($text,$varname=null) {
		return "<!-- {$text} -->";
	}
		
}

class FRAMEWORK  {
	var $obj = object;
	var $worktime = 0;
	var $start_time = 0;
	var $allow_calling = true;
	
	
	var $user_func;
	
	var $map_index = 0;
	var $parent_context;
	
	public function __construct($start_value=null) {
		$this->start_time = micronow();
		$this->obj = new WEB_FRAMEWORK();
		$this->obj->main = $this;
		$this->obj->value = $start_value;
		
		return $this;
	}
	
	public function get() {
		return $this->obj->value;
	}

   private function call_function($function, $arguments, $key = null, $tpl = false) {
   
		if ($tpl) {
			foreach ($arguments as $i => $argument) {
				if ( is_string ($argument) ) {
					$arguments[$i] = produce( $arguments[$i] ,  $this->vars, $key );
				}
			}
		}
		
		
	
		// class methods have priority over global functions
		if (method_exists($this->obj,$function)) {
			$value = call_user_func_array( array( $this->obj, $function ), $arguments );			
		} else if ( function_exists( $function )) {
			// prepend the current stack top value to begining of list of arguments -- only if not null
			if ($this->obj->value!= null) {
				array_unshift( $arguments, $this->obj->value );
			}
			$value = call_user_func_array(  $function , $arguments );
		} elseif ($function=="eval" || $function = "evaluate") {
			$value = "Invalid expression: ".$arguments[0];
			@eval("\$value = ".$arguments[0].";"); //die ("ERR in expression: ".$arguments[0]);
		} else {
			die("Unknown function {$function} ");
		}
		
		
		return $value;
   }
	
   public function __call($function, $arguments) {
		//$call_start_time = micronow();
   		$value = "";
   		// register call for debugging and reconstructing
		# $this->calls[] = array($function);

		// see if calls are allowed
		if ( ! $this->allow_calling ) return $this;
		
		// determine function call context
		$fparts = explode( "_", $function );
		
		$function_prefix = $fparts[0];
		
		if (in_array( $function_prefix , array("MAP","TPL","FILE") ) ) {
			$apply_template = in_array("TPL",$fparts);
			array_shift($fparts);
			$function = implode("_",$fparts);
		}
		
		switch( $function_prefix ) {
			// apply function call to each element
			case "MAP":
				$array = $this->obj->value;
				foreach ($array as $i => $value) {
					$this->obj->value = $value;
					$array[ $i ] = $this->call_function( $function, $arguments , $i , $apply_template  );
				}
				$value = $array;
			break;
			case "SHOW":
				$value = $this->call_function( $function, $arguments , 0 , false );
				$this->obj->show();
			break;
			case "TPL":
				$value = $this->call_function( $function, $arguments , 0 , true );
			break;
			case "FILE":
				$arguments[0] = file_get_contents($arguments[0]);
				$value = $this->call_function( $function, $arguments , 0 , false );
			break;
			default:
				$value = $this->call_function( $function, $arguments , 0 , false );
			break;
		}
		
		
		// if a value has been returned set the value
		if ($value!==null) {
			$this->obj->push($value);
		}
		
		// register worktime
		$this->worktime = microdiff($this->start_time);
		
		
		#$this->calls[count($this->calls) - 1][] = microdiff($call_start_time);
		return $this;
	}
	
	function suspend() {
		$this->allow_calling = false;
		return $this;
	}
	
	// get list of methods
	public function methods() {
		$this->obj->value =  get_class_methods( $this->obj ) ;
		return $this;
	}
	
	public function MAP($function) {
		$this->obj->value = array_map( $function, $this->obj->value ) ;
		return $this;
	}
	
	function transform($func) {
		$this->obj->value = toeach($this->obj->value,$func);
		return $this;
	}
	
	
	
	function __destruct() {
		//echo "Destrying framework<br />";
	}
	
	
	function ret() {
		return $this->obj->value;
	}

		
}



function FW($a=null,$b=null) {
	return new FRAMEWORK($a,$b);
}
?>