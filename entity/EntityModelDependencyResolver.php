<?

interface IDependencyResolver {
	
	public function getDependency( $className , $constructorParams );

}


class EntityModelDependencyResolver implements IDependencyResolver {
	
	public function getDependency( $className , $constructorParams )
	{
		return $className::getInstance();
	}
	
	public function __get( $className )
	{
		return $this->getDependency( ucfirst( $className ) , null );
	}
	
	public function __call( $className , $params )
	{
		
		$instance = call_user_func_array( array( $className , "getInstance" ) , $params  );
			
		return $instance;
	
	}

}

?>