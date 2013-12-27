<?

abstract class ApplicationRouter 
{
	
	protected $routes;
	
	// default route to take when the controller does not provide one via exitEventParam
	protected $defaultExitRoute = null;
	
	public abstract function getViewProvider( $controllerClassName );
	
	
	// redirect to another route when controller exits
	public abstract function redirect( $controller );
	
	public function __construct( $routes ) 
	{
		$this->routes = $routes;
	}
	
	public function route( $url , $templateViewFilename , $notFoundViewFilename ) 
	{
	
		
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
	
	
	private function getControllerForRoute( $url )
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
				
			$viewProvider = $this->getViewProvider( $controllerClassName );

			$this->defaultExitRoute = $defaultExitRoute;
			
			
			// extend controller params with regex matches
			if ( is_array( $matchMapping ) )
			{
				foreach ( $matchMapping as $varName => $index )
				{
				
					$controllerParams[ $varName ] = $matchResults[ $index ];
				
				}
				
			}
				
			$controller = 
				new $controllerClassName
				( 
					null
					, 
					$controllerParams
					,
					$viewProvider					
				);
			
			return $controller;
				
		}
		
		return null;
		
	
	}

}


?>