<?
include_once(dirname(__FILE__)."/../base/Base.php");

interface ISchedulerEventHandler {

	function onAddTask($task,$arguments,$execute_after) ;	
	function onExecuteTask($task,$arguments,$execute_after) ;
	
}

?>