<?

class InMemoryDataFilter implements IDataFilter 
{
	
	private $filterArray;

	
	public static function Resolve( $mixed ) 
	{
		if ( is_array( $mixed ) )  
		{
			$res = new InMemoryDataFilter( $mixed );
		} 
		else if ( $mixed instanceof InMemoryDataFilter ) 
		{
			$res = $mixed;
			
		} else {
			throw new Exception(
				"Cannot resolve to a InMemoryDataFilter: " 
				. var_export( $mixed ,true)
			);
		}
		
		return $res;
	}
	

	public function __construct( $filterArray ) 
	{
	
		$this->filter = $filterArray;
	
	}

	
	public function matches( $entity ) 
	{
	
		// print_r( array('mathcing' , $entity , $this->filter ) );
		// simple matching
	  return $this->filter == array_intersect_assoc ( (array) $entity , $this->filter ) ;
	}



}



?>