<?



class MySQLDataDriver implements IDataDriver
{


	protected $qdp;

	private $_table;
	private $_where;
	private $_order;
	private $_start;
	private $_limit;
	
	public function __construct( $qdp = null ) 
	{
		if ( $qdp === null )
			$qdp = Project::GetQDP();
			
			
		$this->qdp = $qdp;
	
	}
	

	public function find( $entityType , $filterMixed ) 
	{
		$this->_table = $entityType;
		$this->_where = $filterMixed;
		
		return $this;
	}
	
	
	private $comparison;
	
	
	// chains
	public function orderBy( $comparisonMixed ) 
	{
		$this->_order = $comparisonMixed;
		return $this;
	}
	
	
	// chains
	public function limit( $start,  $limit ) 
	{
		$this->_start = intval( $start );
		$this->_limit = intval( $limit );
		
		return $this;
	}
	
	
	
	// releases chain
	public function yield() 
	{
	
		$queryFilter = SQLFilter::Create();
		
		
		if ( $this->_where != null ) 
		{
			$queryFilter->setWhere( $this->_where );
		}
		
		
		if ( $this->_order != null ) 
		{
			$order = $this->_order;
			foreach ( $order as $field => $direction ) {
				if ( $direction == -1 ) {
					$order[ $field ] = 'desc';
				} else {
					$order[ $field ] = 'asc';
				}
			}
			$queryFilter->setOrder( $order );
		}
		
		if ( $this->_limit != null ) 
		{
			$queryFilter->setLimit( "{$this->_start}, {$this->_limit}" );
		}
		
		
		$table = mysql_real_escape_string( $this->_table );
		
		$queryTop = "select * from `{$table}` ";		
		$queryBottom = $queryFilter->toString();
		
		
		// construct query
		$query = $queryTop . $queryBottom;
		
		// reset to old state
		$this->_table = null;
		$this->_where = null;
		$this->_order = null;
		$this->_start = null;
		$this->_limit = null;
	
	
		// execute query and gather results
		return $this->qdp->execute( $query )->toArray();
		
	}
	
	public function update( $entityType , $entity ) 
	{
		return $this->qdp->update(
			$entityType , 
			$entity , 
			SQLFilter::Create()->setWhere(array( 'id' => $entity['id'] )) 
		);
	}
	
	public function insert( $entityType , $entity ) 
	{
	
		
		$this->qdp->insert( $entityType , $entity );
		
		echo $this->qdp->getError();
		
		return $this->qdp->getAffectedRowCount();
	
	}
	
	
	
	public function count( $entityType ) 
	{
		// todo: make prepared statement
		$entityType = mysql_real_escape_string( $entityType );
		return $this->qdp->execute("select count(*) c from `{$entityType}`")->toCell();
	}
	
	
	public function delete( $entityType , $entityArray ) 
	{
		return $this->qdp->delete(
			$entityType , 			
			SQLFilter::Create()->setWhere(array( 'id' => $entityArray['id'] ))
		);
	} 
	

}


?>