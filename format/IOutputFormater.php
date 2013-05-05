<?
include_once(dirname(__FILE__).'/../base/Base.php');

interface IOutputFormater {
	function Initialize(); 
	function Format($data);
}

?>