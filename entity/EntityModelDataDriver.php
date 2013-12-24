<?


// used for magically extending new driver methods for each model

abstract class EntityModelDataDriver implements IDataDriver 
{

	public abstract function runUserDriverMethod( $methodName , $args );
	
	
	public function __call( $method, $args ) 
	{
		return $this->runUserDriverMethod( $method, $args );
	
	}
	
}



?>