<?

class InMemoryDataDriver implements IDataDriver
{

	protected $data = array();
	
	protected $resultSet = array();


	public function find( $entityType , $filterMixed ) 
	{

		// using an in memory data filter
		$filter = InMemoryDataFilter::Resolve( $filterMixed );
	
		$this->resultSet = array();
		
		
		foreach ( $this->data as $row ) 
		{
			if ( $filter->matches( $row ) )
			{
				$this->resultSet[] = $row;
			}	
		}
		
		
		return $this;	
	
	}
	
	
	
	private $comparison;
	
	public function internalCompare( $a , $b ) 
	{
		
		foreach ( $this->comparison as $field => $direction ) 
		{
			
			$isEqual = false;
			$needsSwap = false;
			
			if ( $a[ $field ] == $b[ $field ] ){
				$isEqual = true;
			} 
			else if ( $direction < 0 || $direction == 'desc' ) 
			{
				$needsSwap = $a[ $field ] < $b[ $field ];
			} 
			else 
			{
				$needsSwap = $a[ $field ] > $b[ $field ];
			}
			
			if ( $isEqual ) {
				continue;
			} 
			
			return $needsSwap;
		}
		
		return false;
	
	}
	
	
	
	// chains
	public function orderBy( $comparisonMixed ) 
	{
			
		$this->comparison = $comparisonMixed;
		
		usort( $this->resultSet , array( $this , 'internalCompare' ) );
	
	
		return $this;
	
	}
	
	
	// chains
	public function limit( $start,  $limit ) 
	{
		
		$this->resultSet = array_slice( $this->resultSet , $start, $limit , false );
		
		return $this;
	
	}
	
	
	
	// releases chain
	public function yield() 
	{
	
	
		$results = $this->resultSet;
		
		$this->resultSet = array();
		
		return $results;
	}
	

	public function insert( $entityType , $entity ) 
	{
	
		$size = count($this->data);
		
		$this->data[] = $entity;
		
		return  count($this->data) - $size;
	
	}
	
	
	
	public function count( $entityType ) 
	{
		return count( $this->data );
	}
	
	
	public function update( $entityType , $entity ) 
	{
		foreach ( $this->data as $i=>$row ) 
		{
			if ( $row['id'] == $entity['id'] ) 
			{
				$this->data[ $i ] = $entity;
				
				return 1;
			}
		}
		
		return 0;
		
	}
	
	
	public function delete( $entityType , $entity ) 
	{
		foreach ( $this->data as $i => $row ) 
		{
			if ( $row['id'] == $entity['id'] ) 
			{
				unset( $this->data[ $i ] );
				
				$this->data = array_values( $this->data );
				
				return 1;
			}
		}
		
		return 0;
	} 
	
	
}


?>