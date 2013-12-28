<?

abstract class ApplicationRouter 
{
	
	protected $routes;
	
	// default route to take when the controller does not provide one via exitEventParam
	protected $defaultExitRoute = null;
	
		
	// route to the controller
	public abstract function route( $url , $params );
	
	// redirect to another route when controller exits
	public abstract function redirect( $controller );
	
	// get the controller once route is found
	public abstract function getController( $controllerClassName , $controllerParams );
		
	
	public function __construct( $routes ) 
	{
		$this->routes = $routes;
	}
	
		
	
	
	protected function getControllerForRoute( $url )
	{
	
		$routes = $this->routes;
		
		
		$controllerMatched = false;
		
		// direct match
		if ( array_key_exists( $url , $routes ) ) 
		{
		
			$controllerMatched = true;
			
			list( $controllerClassName , $controllerParams , $matchMapping , $defaultExitRoute ) = $routes[ $url ];
			
		
		} 
		else 
		{
		// regex match
			
			foreach ( $routes as $pattern => $routeInstructions ) 		
			{
			
				list( $className , $controllerParams , $matchMapping , $defaultExitRoute ) = $routeInstructions;
				
				
				if ( @preg_match(  "/{$pattern}/"  , $url , $matchResults ) ) 
				{
					
					$controllerClassName = $className;
					
					$controllerMatched = true;
					
					break;
					
				}
						
			}
			
		
		}
		
		
		
		if ( $controllerMatched )
		{
			

			$this->defaultExitRoute = $defaultExitRoute;
			
			
			// extend controller params with regex matches
			if ( is_array( $matchMapping ) )
			{
				foreach ( $matchMapping as $varName => $index )
				{
				
					$controllerParams[ $varName ] = $matchResults[ $index ];
				
				}
				
			}
			
			$controller = $this->getController( $controllerClassName , $controllerParams );
			
			return $controller;
				
		}
		
		return null;
		
	
	}

}


?>