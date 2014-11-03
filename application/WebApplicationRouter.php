<?

abstract class WebApplicationRouter extends ApplicationRouter
{


	
	public abstract function getViewProvider( $controllerClassName );
	
	
	public static function exportvars( $vars ){
		return var_export( $vars, true );
	}
		
	public function route( $url , $params ) 
	{
	
		list( $templateViewFilename , $notFoundViewFilename , $errorViewFilename ) = $params;
		
		try 
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
					header('HTTP/1.1 200 Ok');
					return produceview( $templateViewFilename,  $controller );
					
				}
			} 
			else 
			{
				header('HTTP/1.1 404 Not Found');
				return produceview( $notFoundViewFilename , array( "url" => $url ) );
			}
		} 
		catch( Exception $ex ) 
		{
			
			header('HTTP/1.1 500 Internal Server Error');
			header('Content-type:text/plain');
			
			print_r( "Exception\r\n\r\n" );
			print_r( $ex->getMessage() ." (Exception code: {$ex->getCode()})\r\n" );
			print_r("\r\n");
			print_r( "Thrown at". $ex->getFile() . '(' . $ex->getLine() ."):\r\n\r\n" );
			
			
			$tpl="\${#[#] [file]([line]):\r\n[class][type][function]($[, ]([args]){[*:WebApplicationRouter::exportvars]}) \r\n---------------------\r\n}";
			
			print_r( produce( $tpl , $ex->getTrace() ));
			
			die();
		
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