<?

abstract class WebApplicationRouter extends ApplicationRouter
{

	public function redirect( $controller )
	{
		
		$exitEventName =  $controller->exitEventName;
		
		if ( $controller->exitEventParam != null )
		{
			$url = $controller->exitEventParam;
		} 
		else if ( $this->defaultExitRoute != null ) 
		{
			$url = $this->defaultExitRoute;
		} 
		else 
		{
			throw new Exception("No exitEventParam or default exite route specified for " . get_class($controller) );
		}
		
		
		header('location:' . $url);
		die();
	
	}



}



?>