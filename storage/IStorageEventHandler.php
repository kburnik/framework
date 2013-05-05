<?
include_once(dirname(__FILE__)."/../base/Base.php");

interface IStorageEventHandler extends IEventHandler {
	
	function onRead($variable);
	
	function onWrite($variable,$value);
	
	function onClear($variable);
	
	function onLoad($data);
	
	function onStore($data);

}

?>