<?
include_once("testing.module.php");

$auxiliary_testing = array(
	"javascript" => function() {
		$single = "jquery.js";
		$array = array("main.js","aux.js","article.js");
		$result_single = javascript($single);
		$result_array = "\n".javascript($array,2);
		return array(
			"single"=>$single,
			"array"=>$array,
			"result_single"=>htmlentities($result_single),
			"result_array"=>htmlentities($result_array)
		);
	},
	"css" => function() {
		$single = "style.css";
		$array = array("main.css","aux.css","article.css");
		$result_single = css($single);
		$result_array = "\n".css($array,2);
		return array(
			"single"=>$single,
			"array"=>$array,
			"result_single"=>htmlentities($result_single),
			"result_array"=>htmlentities($result_array)
		);
	}
);

runtest($auxiliary_testing);
?>