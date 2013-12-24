<?

abstract class EntityModel extends BaseSingleton
{


	// name of the main entity this EnityModel represents
	protected $entityClassName = null;
	
	// the IDataDriver object which communicates to the data source ( i.e. Database/InMemory/FileSystem )
	protected $dataDriver = null;
	
	public function __construct() 
	{
		parent::__construct();
		
		$this->dataDriver = $this->getDataDriver();	
		$this->entityClassName = $this->getEntityClassName();
	}
	
	
	private static $modelInstances = array();
	
	public static function getInstance()  
	{
		$entityModelClassName = get_called_class();
		if (!isset(self::$modelInstances[ $entityModelClassName ])) 
		{
			self::$modelInstances[ $entityModelClassName ] = new $entityModelClassName();
		}
		return self::$modelInstances[ $entityModelClassName ];
	}
	
	
	public function __call( $method,  $args ) 
	{
			
	
		if ( substr( $method , 0 ,2 ) == '__' )
		{
		
			$driverMethodName = substr( $method , 2 );
		
			if ( !method_exists( $this->dataDriver , $driverMethodName ) ) 
			{
			
				$dataDriverClassName = get_class( $this->dataDriver );
			
				throw new Exception( "Missing method for {$dataDriverClassName}::{$method}" );
				
			}
			
			$result = call_user_func_array(
				array( $this->dataDriver , $driverMethodName )
				, 
				$args
			);
			
			return $this->toObjectArray( $result );
			
		} 
		else 
		{
			parent::__call( $method, $args );
		}
		
		
		
		
	}
	
	protected function getDataDriver( ) 
	{
		
		if ( !isset( $this->dataDriver ) )
		{
			$entityModelClassName = get_class( $this );
		
			$dataDriverClassName = "{$entityModelClassName}DataDriver";
			
			if ( ! class_exists( $dataDriverClassName ) )
			{
				throw new Exception("Missing Data Driver '{$dataDriverClassName}'");
			
			}
			
			$this->dataDriver = new $dataDriverClassName();
		
		}
		
		
		
		return $this->dataDriver;
	
	}
	
	
	protected function getEntityClassName() 
	{
		if ( $this->entityClassName === null ) 
		{
			$className = get_class( $this );
			
			$this->entityClassName = preg_replace('/(.*)Model/','$1',$className);
					
		}
		
		
		return $this->entityClassName;
	}
	
	protected final function getEntityPublicFields()
	{
		
		$reflect = new ReflectionClass( $this->entityClassName );
		
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
		
		$fields = $this->getEntityPublicFields();
		
		if (! $filterKeys == array_intersect(  $filterKeys , $fields )) 
		{
			$diff = array_diff( $filterKeys, $fields );
			throw new Exception("Invalid filter, some fields don't exist: " 
			. var_export( $diff , true ));
		}
	
	}
	
	protected function resolveEntityAsArray( $entityMixed ) 
	{
		if ( ! is_array( $entityMixed ) ) 
		{
			$entityArray = $entityMixed->toArray();
		} 
		else 
		{
			$entityArray = $entityMixed;
		}
		
		return $entityArray;
	}
	
	
	private function _insertSingleEntity( $entityMixed ) 
	{
	
		
		if (is_array( $entityMixed ) || $entityMixed instanceOf $this->entityClassName  ) 
		{
			
			
			$entityArray = $this->resolveEntityAsArray( $entityMixed );
			
			return $this->getDataDriver()->insert( $this->entityClassName ,  $entityArray );
			
		} 
		else 
		{
		
			throw new Exception(
				"Cannot insert object to model . Expected '{$this->entityClassName}'"
				. " or array of such. Got " . var_export($entityMixed, true) );
		}
	}
	
	
	public function count() 
	{
	
		return $this->getDataDriver()->count( $this->entityClassName );
		
	}
	


	// create entity from array
	public function create( $entityArray = array() ) 
	{

		$entityObject = new $this->entityClassName( $entityArray );
		
		return $entityObject;
		
	}
	
	
	
	
	
	
	// can be one article as array or object, or an array of article array/objects
	public function insert( $mixed ) 
	{
		
		if ( is_array( $mixed ) && count( $mixed ) > 0 ) {
			$firstItem = reset( $mixed );
			
			if ( is_array($firstItem) || $firstItem instanceOf $this->entityClassName  ) {
				$total = 0;
				foreach ( $mixed as $item ) 
					$total += $this->_insertSingleEntity( $item );
					
				return $total;
			}
		
		}
		
		return $this->_insertSingleEntity( $mixed );
	
	}
	
	
	// update a single entity
	public function update( $entityMixed ) 
	{
		
		$entityArray = $this->resolveEntityAsArray( $entityMixed );
				
		return $this->getDataDriver()->update( $this->entityClassName , $entityArray );
		
	}
	
	// delete a single entity
	public function delete( $entityMixed ) 
	{
	
		$entityArray = $this->resolveEntityAsArray( $entityMixed );
		
		return $this->getDataDriver()->delete( $this->entityClassName , $entityArray );
	}
	

	
	public function findById( $id ) 
	{
		
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
	
		// chain start		
		$this->dataDriver->find( $this->entityClassName , $filterArray );
		
		return $this;		
		
	}
	
	
	
	
	public function orderBy( $comparison ) 
	{
	
		if ( ! ($this->dataDriver instanceOf IDataDriver ) ) 
		{
			throw new Exception('Cannot sort, no selection made');
		}
	
		$this->dataDriver->orderBy( $comparison );
		
		return $this;
	
	}
	
	
	public function limit( $start,  $limit ) 
	{
	
		if ( ! ($this->dataDriver instanceOf IDataDriver ) ) 
		{
			throw new Exception('Cannot limit, no selection made');
		}
	
		$this->dataDriver->limit( $start,  $limit );
		
		return $this;
	
	}
	
	
	protected function toObjectArray( $array ) 
	{
	
		foreach ( $array as $i => $entityArray ) 
		{
			$array[$i] = $this->create( $entityArray );
		}
		
		return $array;
		
	}
	
	
	// release the chain
	public function yield() 
	{
		$results = $this->dataDriver->yield();
		
		return $this->toObjectArray( $results );
		
	}
	

}



?>