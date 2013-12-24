<?


abstract class EntityModel 
{


	protected $entityClassName = null;
	
	
	private static $entityDriver;
	
	public static function SetDefaultDataDriver( $entityDriver ) 
	{
		self::$entityDriver = $entityDriver;
	}
	
	
	public function getDataDriver( ) 
	{
		
		if ( ! self::$entityDriver instanceOf IDataDriver  ) 
		{
			throw new Exception( "Entity Driver not set" );
		}
		
		return self::$entityDriver;
	
	}
	
	
	
	protected function _getEntityClassName() 
	{
		if ( $this->entityClassName === null ) 
		{
			$className = get_class( $this );
			
			$entityClassName = preg_replace('/(.*)Model/','$1',$className);
			
			$this->entityClassName = $entityClassName;
		}
		
		
		return $this->entityClassName;
	}
	
	protected function _getEntityPublicFields()
	{
		$entityClassName = $this->_getEntityClassName();
		
		$reflect = new ReflectionClass( $entityClassName );
		
		$props = $reflect->getProperties( ReflectionProperty::IS_PUBLIC );
		
		$fields = array();
		
		foreach ($props as $key => $prop) 
		{
			$propname = $prop->getName();
			$fields[] = $propname;
		}
		
		return $fields;
	
	}
	
	protected function _checkFilter( $filterArray ) 
	{
	
		if (!is_array( $filterArray )) {
			throw new Exception("Expected array for filter, got : " 
			. var_export( $filterArray , true ));
		}
		
		
		$filterKeys = array_keys( $filterArray );
		
		$fields = $this->_getEntityPublicFields();
		
		if (! $filterKeys == array_intersect(  $filterKeys , $fields )) 
		{
			$diff = array_diff( $filterKeys, $fields );
			throw new Exception("Invalid filter, some fields don't exist: " 
			. var_export( $diff , true ));
		}
	
	}
	
	
	private function _insertSingleEntity( $entity  ) 
	{
	
		$entityClassName = $this->_getEntityClassName();
		
		if (is_array( $entity ) || $entity instanceOf $entityClassName  ) 
		{
			
			if ( ! is_array($entity) ) 
			{
				$entityArray = $entity->toArray();
			} 
			else 
			{
				$entityArray = $entity;
			}
			
			return $this->getDataDriver()->insert( $entityClassName ,  $entityArray );
			
		} 
		else 
		{
		
			throw new Exception(
				"Cannot insert object to model . Expected '{$entityClassName}'"
				. " or array of such. Got " . var_export($entity, true) );
		}
	}
	
	
	public function count() 
	{
	
		$entityClassName = $this->_getEntityClassName();
		
		return $this->getDataDriver()->count( $entityClassName );
		
	}
	


	// create entity from array
	public function create( $entityArray = array() ) 
	{

		$entityClassName = $this->_getEntityClassName();
	
		return new $entityClassName( $entityArray );
		
	}
	
	
	
	
	
	
	// can be one article as array or object, or an array of article array/objects
	public function insert( $mixed ) 
	{
		
		if ( is_array( $mixed ) && count( $mixed ) > 0 ) {
			$firstItem = reset( $mixed );
			
			$entityClassName = $this->_getEntityClassName();
			
			if ( is_array($firstItem) || $firstItem instanceOf $entityClassName  ) {
				$total = 0;
				foreach ( $mixed as $item ) 
					$total += $this->_insertSingleEntity( $item );
					
				return $total;
			}
		
		}
		
		return $this->_insertSingleEntity( $mixed );
	
	}
	
	
	// update a single entity
	public function update( $mixed ) 
	{
		if ( !is_array( $mixed ) ) 
		{
			$entityArray = $mixed->toArray();
		
		} else {
			$entityArray = $mixed;
		}
		
		$entityClassName = $this->_getEntityClassName();
		
		return $this->getDataDriver()->update( $entityClassName , $entityArray );
		
	}
	
	// delete a single entity
	public function delete( $mixed ) 
	{
	
		if ( !is_array( $mixed ) ) 
		{
			$entityArray = $mixed->toArray();
		
		} else {
			$entityArray = $mixed;
		}
		
		$entityClassName = $this->_getEntityClassName();
		
		return $this->getDataDriver()->delete( $entityClassName , $entityArray );
	}
	

	
	public function findById( $id ) 
	{
		
		
		$entityClassName = $this->_getEntityClassName();
		
		$results = $this->find( array( 'id' => $id ) )->yield();
			
		if ( count( $results ) > 0 ) 
		{
			return reset( $results );
		} 
		else 
		{
			return null;
		}
	
	}
	
	
	
	public function findFirst( $filterArray ) 
	{
	
		$results = $this->find( $filterArray )->yield();
	
		if ( count( $results ) > 0 ) 
		{
			return reset( $results );
		} 
		else 
		{
			return null;
		}
		
		
	}
	
	private $results = array();
	
	
	
	// chains
	public function find( $filterArray = array() ) 
	{
	
		$this->_checkFilter( $filterArray );
	
		$entityClassName = $this->_getEntityClassName();
		
		// chain start
		$this->driver = $this->getDataDriver();
		
		$this->driver = $this->driver->find( $entityClassName , $filterArray );
		
		return $this;		
		
	}
	
	
	
	
	public function orderBy( $comparison ) 
	{
	
		if ( ! ($this->driver instanceOf IDataDriver ) ) 
		{
			throw new Exception('Cannot sort, no selection made');
		}
	
		$this->driver = $this->driver->orderBy( $comparison );
		
		return $this;
	
	}
	
	
	public function limit( $start,  $limit ) 
	{
	
		if ( ! ($this->driver instanceOf IDataDriver ) ) 
		{
			throw new Exception('Cannot limit, no selection made');
		}
	
		$this->driver = $this->driver->limit( $start,  $limit );
		
		return $this;
	
	}
	
	

	
	
	// release the chain
	public function yield() 
	{
		$results = $this->driver->yield();
		
		$entityClassName = $this->_getEntityClassName();
		
		foreach ( $results as $i=>$res ) 
		{
			$results[$i] = $this->create( $res );
		}
		
		return $results;	
	}
	
	
	
	

}



?>