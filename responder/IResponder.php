<?
include_once(dirname(__FILE__).'/../base/Base.php');

interface IResponder {
	function respond($formatter = 'VarExportFormater') ;
	function handleArgumentException($argument) ;
	function handleMethodException($method) ;
	function handleError($message,$code);
	function setMessage($message);
}

?>