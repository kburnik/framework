<?
include_once(dirname(__FILE__)."/../base/Base.php");

interface IApplicationEventHandler extends IEventHandler  {

	public function onStart();
	
	public function onShutdown();	
}

?>