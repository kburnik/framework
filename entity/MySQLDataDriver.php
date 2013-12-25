<?



class MySQLDataDriver implements IDataDriver
{


	protected $qdp;

	private $_fields;
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
	public function select( $entityType , $fields ) 
	{
		$this->_fields = $fields;
		
		return $this;
	
	}

	
	
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
	
	
	protected function operatorBetween( $entity , $params ) 
	{
	
		list( $field, $from , $to ) = $params;
		
		$field = mysql_real_escape_string($field);
		$from = mysql_real_escape_string($from);
		$to = mysql_real_escape_string($to);
		
		return " `{$field}` between \"{$from}\" and \"{$to}\" ";	
	}
	
	protected function operatorIn( $entity , $params ) 
	{
	
		list( $field, $values ) = $params;
		
		$field = mysql_real_escape_string($field);
		$values = produce('$[,]{"[*:mysql_real_escape_string]"}',$values);
		
		return " `{$field}` in ( {$values} ) ";	
	}
	
	
	protected function operatorNin( $entity , $params ) 
	{
	
		list( $field, $values ) = $params;
		
		$field = mysql_real_escape_string($field);
		$values = produce('$[,]{"[*:mysql_real_escape_string]"}',$values);
		
		return " `{$field}` not in ( {$values} ) ";
	
	}
	
	private function singleParamOperator( $entity , $params , $operator )
	{
		
		list( $field, $val ) = $params;
		$field = mysql_real_escape_string($field);
		$val = mysql_real_escape_string($val);		
		return " `{$field}` {$operator} '{$val}'";	
		
	}
	
	
	protected function operatorEq( $entity , $params ) 
	{
		return $this->singleParamOperator( $entity , $params, '=' );
	}
	
	
	protected function operatorNe( $entity , $params ) 
	{
		return $this->singleParamOperator( $entity , $params, '!=' );
	}
	
	protected function operatorGt( $entity , $params ) 
	{
		return $this->singleParamOperator( $entity , $params, '>' );
	}
	
	
	protected function operatorLt( $entity , $params ) 
	{
		return $this->singleParamOperator( $entity , $params, '<' );		
	}
	
	
	protected function operatorGtEq( $entity , $params ) 
	{
		return $this->singleParamOperator( $entity , $params, '>=' );
	}
	
	
	protected function operatorLtEq( $entity , $params ) 
	{
		return $this->singleParamOperator( $entity , $params, '<=' );		
	}
	
	
	
	
	
	
	
	private function createWhereClause( $queryFilter )
	{
		
		$filterArray = $this->_where;
	
		foreach ( $filterArray  as $var => $val ) 
		{
		
			if ( $var[0] == ':' ) 
			{
			
				$operatorName = substr($var,1);
				
				$operatorMethodName = "operator{$operatorName}";
				
				$operation = $this->$operatorMethodName( null , $val );
				
				
				$queryFilter->appendWhere( $operation );
			
			} else {
			
				$var = mysql_real_escape_string( $var );
				$val = mysql_real_escape_string( $val );
				
				$queryFilter->appendWhere( "`{$var}` = \"{$val}\"" );
			}
		
		}
		
	
		
	
	
	}
	
	
	
	private function createFilter()
	{
		$queryFilter = SQLFilter::Create();
		
		if ( $this->_fields != null )
		{
			$queryFilter->setFields( $this->_fields );
		
		}
		
		if ( $this->_where != null ) 
		{
		
			$this->createWhereClause( $queryFilter );		
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
		
		return $queryFilter;
	
	}
	
	private function reset()
	{
		
		$this->_fields = null;
		$this->_table = null;
		$this->_where = null;
		$this->_order = null;
		$this->_start = null;
		$this->_limit = null;
	
	}
	
	private function constructQuery( ) 
	{
		$queryFilter = $this->createFilter();
		
		$table = mysql_real_escape_string( $this->_table );
		
		$filter = $queryFilter->toString();
		
		$fields = $queryFilter->getFields();
		
		// construct query
		$query = "select {$fields} from `{$table}` {$filter} ";	
				
		return $query;	
	
	}
	
	
	// releases chain
	public function yield() 
	{
	
		$query = $this->constructQuery( );
		
		// reset to old state
		$this->reset();
		
		// execute query and gather results
		return $this->qdp->execute( $query )->toArray();
		
	}
	
	
	public function affected() 
	{
	
		$this->_fields = "count(id)";
		
		$query = $this->constructQuery();
		
		// reset to old state
		$this->reset();
		
		// execute query and gather results
		return $this->qdp->execute( $query )->toCell();
		

	
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