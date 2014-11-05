<?


interface IApplicationEventHandler extends IEventHandler  {

  public function onStart();
  
  public function onShutdown();  
}

?>