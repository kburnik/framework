<?

interface IDataDriver 
{

	
	public function update( $entityType , $entityArray );
	
	public function insert( $entityType , $entityArray );
	
	public function delete( $entityType , $entityArray ); // id or entity object
	
	
	public function count( $entityType );	
	
	
	// chain
	public function find( $entityType , $filter );
	
	// chain
	public function orderBy( $comparisonMixed );
		
	// chain
	public function limit( $start , $limit );
	
	// Release the chain : return the result of the lasy operation
	public function yield();
	
	

}


?>