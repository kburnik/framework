<?

abstract class Entity extends ArrayAccessible
{

	public function __construct( $mixed = null )  
	{
	
		if ( is_array($mixed) )
		{
			$this->fromArray( $mixed );
		}
	
	
	}
	
	public final function getFields()
	{
		
		$reflect = new ReflectionClass($this);
		
		$props = $reflect->getProperties(ReflectionProperty::IS_PUBLIC);
		
		$fields = array();
		
		foreach ($props as $prop)
		{
			$fields[] = $prop->getName();			
		}
		
		return $fields;		
	}

	public function toArray() 
	{
		$reflect = new ReflectionClass($this);
		$props   = $reflect->getProperties(ReflectionProperty::IS_PUBLIC);
		$values = array();
		foreach ($props as $prop) 
		{
			$propname = $prop->getName();
			$values[  $propname ]   = $this->$propname;
		}
		return $values;
	}
	
	public function fromArray( $data ){
		$publicFields = array_keys($this->toArray());
		foreach ($data as $field => $value) {
			if ( in_array( $field, $publicFields ) ) 
			{
				$this->$field = $value;
			}
		}
	}
	
	// magic getters and setters for referencing entities
	
	
	public function __get( $var )
	{
		$getterName = "get{$var}";
		if ( method_exists( $this , $getterName  ) ) 
		{
			return $this->$getterName();
		}
	}
	
	public function __set( $var , $val ) 
	{
		
		$setterName = "set{$var}";
		if ( method_exists( $this , $setterName  ) ) 
		{			
			return $this->$setterName( $val );
		}
	}

}


?>