<?
include_once("system.php");



$table = "articles";

echo(javascript("../js/jquery.js"));


FW() 
-> push (sql() -> field_details($table) )  -> rotate_table() -> show_struct() -> show();
;

FW() 
-> push (sql() -> field_details($table) )  -> rotate_table() -> show_struct() -> show();
;


die();
$tbl =  FW() 
-> push (sql() -> field_details($table) ) -> rotate_table() -> array_pick("Field","Comment") -> rotate_table() -> output()
;
$tbl = sql("select * from articles limit 5;") -> arr();
// echo "<pre>";
// $tbl = $_SERVER;
foreach ($tbl as $i=>$r) {
	$tbl[$i]["copy"] = $r;
}

echo (show_struct($tbl));



//

class _tags  {
	function __construct() {
	
	}
	
	function __call($name,$arguments){
		$attributes = $arguments[0];
		$html = $arguments[1];
		return $this->construct_tag($name,$attributes,$html);
	}
	
	function construct_tag($name,$attributes=array(),$html=null) {
	
		switch($name) {
			case "input":
			
			break;
		}
		
		if (is_array($attributes) && count($attributes)>0) {
			foreach($attributes as $attr=>$value) {
				$value = htmlentities($value);
				$attrs[]="{$attr}=\"{$value}\"";
			}
			$attrs = " ".implode(" ",$attrs);
		} else {
			if (!is_array($attributes) && $html===null) {
				$html = $attributes;
			}
			$attrs="";
		}
		
		
		if ($html===null) {
			$attrs .= " ";
			$tag = "<{$name}{$attrs}/>";
		} else {
			$tag  = "<{$name}{$attrs}>{$html}</{$name}>";
		}
		
		return $tag;
	}

}


function tags() { 
	global $_tags; 
	if (!isset($_tags)) {
		$_tags = new _tags();
	}
	return $_tags; 
}

die();

class _form {
	
	var $inputs;
	var $name='';
	var $method='post';
	var $action='';
	
	function __construct($name=null,$action="",$method='post') {
		$this->name = $name;
		$this->method = $method;
		$this->action = $action;
		
	}
	
	function __destruct() {
	
	}
	
	function push($value=null){
		$this->inputs[]=$value;
		return $this;
	}
	
	function input($name,$type='text',$value='',$label=null) {
		$id = "{$this->name}_{$name}";
		
		if ($label != null) {
			$t = tags()->label(array("for"=>$id),$label);
			$this->push($t);
		}
		$t = tags() 
		-> input(array(
			"name"=>$name,
			"type"=>$type,
			"value"=>$value,
			"id"=>$id
		));
		$this->push($t);
		return $this;
	}
	
	function label($html='') {
		$t = tags() -> label($html);
		$this->push($t);
		return $this;
	}
	
	function output(){
		$inputs =  implode("\n\t",$this->inputs);
		
		$form = tags()->form(array(
			"name"=>$this->name,
			"method"=>$this->method,
			"action"=>$this->action
		),"\n\t".$inputs."\n");
		return $form;
	}

}



function form($name=null) {
	return new _form($name);
}



$form = form("sample") 
	-> input("name","text","some text and \" esacping ","Enter your name:")
	-> output();

echo $form;
echo tags()->pre(htmlentities($form));

$table = "articles";
$table =  FW() 
-> push (sql() -> field_details($table) ) -> rotate_table() -> array_pick("Field","Comment") -> rotate_table() -> output()
;
echo tags() -> pre(var_export($table,true));
;




/*	
	form("users")
		-> class("my_form_class")
		-> template("
			[name] [surname] <br />
			[input]
		")
		-> attributes("name","id","value","")
		-> autoinputs("users")
		-> input("action","hidden","users")
		-> label("name") -> input("name","text","")
		-> label("surname") -> input ("surname","text","")
		-> label("date")
		-> success(function($data){
			
		})
		-> error(function() {
		
		})
		-> output();
*/	

?>