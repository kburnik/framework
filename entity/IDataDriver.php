<?

interface IDataDriver 
{

	
	public function update( $entityType , $entityArray );
	
	public function insert( $entityType , $entityArray );
	
	public function insertupdate( $entityType , $entityArray );
	
	public function delete( $entityType , $entityArray ); // id or entity object
	
	public function deleteBy( $sourceObjectName , $filterArray );
	
	
	public function count( $entityType );	
	
	
	// chain
	public function find( $entityType , $filter );
	
	// chain
	public function select( $entityType , $fields );
	
	// chain
	public function orderBy( $comparisonMixed );
		
	// chain
	public function limit( $start , $limit );
	
	// Release the chain : return the result of the lasy operation
	public function ret();
	
	// counts affected entries
	public function affected();
	
	
	// return the entity field used for constructing the underlying data structure (e.g. mysql table)
	public function getEntityField();
	
	// chain
	public function join( $sourceObjectName, $refDataDriver , $refObjectName , $resultingFieldName , $joinBy , $fields = null );
	
	
}


?>