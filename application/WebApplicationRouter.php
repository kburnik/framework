<?

abstract class WebApplicationRouter extends ApplicationRouter
{


	
	public abstract function getViewProvider( $controllerClassName );
		
		
	public function route( $url , $params ) 
	{
	
		list( $templateViewFilename , $notFoundViewFilename ) = $params;
		
		$controller = $this->getControllerForRoute( $url );			
		
		if ( $controller instanceOf Controller )
		{
		
			if ( $controller->exited )
			{
				$this->redirect( $controller );
			} 
			else 
			{
				return produceview( $templateViewFilename,  $controller );
			}
		} 
		else 
		{
			header('HTTP/1.1 404 Not Found');
			return produceview( $notFoundViewFilename , array( "url" => $url ) );
		}

	
	}

	
	public function getController( $controllerClassName , $controllerParams )
	{
		
		$viewProvider = $this->getViewProvider( $controllerClassName );
			
			
		$controller = new $controllerClassName
				( 
					null
					, 
					$controllerParams
					,
					$viewProvider					
				);
		
		return $controller;
	
	}

	

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