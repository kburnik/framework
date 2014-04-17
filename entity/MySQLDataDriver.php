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
	private $_joins = array();
	
	public function __construct( $qdp = null ) 
	{
		if ( $qdp === null )
			$qdp = Project::GetQDP();
		
		
			
		$qdp->addEventListener( 'onError' , array($this,onError));

		$this->qdp = $qdp;
	
	}
	
	public function onError( $query , $error , $errnum )
	{
	
		throw new Exception( "$error ($errnum)\n$query\n" );	
	}
	

	public function find( $sourceObjectName , $filterMixed ) 
	{
		$this->_table = $sourceObjectName;
		$this->_where = $filterMixed;
		
		return $this;
	}
	
	
	private $comparison;

	// chains
	public function select( $sourceObjectName , $fields ) 
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
		
		return " __targetEntity.`{$field}` between \"{$from}\" and \"{$to}\" ";	
	}
	
	protected function operatorIn( $entity , $params ) 
	{
	
		list( $field, $values ) = $params;
		
		if ( count( $values ) == 0 )
		{
			return "1=0";
		}		
		
		$field = mysql_real_escape_string($field);
		$values = produce('$[,]{"[*:mysql_real_escape_string]"}',$values);
		
		return " __targetEntity.`{$field}` in ( {$values} ) ";	
	}
	
	
	protected function operatorNin( $entity , $params ) 
	{
	
		list( $field, $values ) = $params;
		
		if ( count( $values ) == 0 )
		{
			return "1=1";
		}
		
		$field = mysql_real_escape_string($field);
		$values = produce('$[,]{"[*:mysql_real_escape_string]"}',$values);
		
		return " __targetEntity.`{$field}` not in ( {$values} ) ";	
	}
	
	private function singleParamOperator( $entity , $params , $operator )
	{
		
		list( $field, $val ) = $params;
		$field = mysql_real_escape_string($field);
		$val = mysql_real_escape_string($val);		
		return " __targetEntity.`{$field}` {$operator} '{$val}'";	
		
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
	
	
	
	
	
	
	
	private function createWhereClause( $queryFilter , $useTargetEntityPrefix = true )
	{
		
		if ( $useTargetEntityPrefix )
			$prefix = "__targetEntity.";
			
			
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
				
				if ( !is_array($val) )
				{
					
					$val = mysql_real_escape_string( $val );
				
					$queryFilter->appendWhere( "{$prefix}`{$var}` = \"{$val}\"" );
				} 
				else 
				{
					// 'like' implementation 
					$val = reset( $val );
					$queryFilter->appendWhere( "{$prefix}`{$var}` like \"{$val}\"" );
				}
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
		$this->_joins = array();
	
	}
	
	private function constructQuery( ) 
	{
		$queryFilter = $this->createFilter();
		
		$table = mysql_real_escape_string( $this->_table );
		
		$filter = $queryFilter->toString();
		
		$fields = $queryFilter->getFields();
		
		if ( $fields == "*" ) 
			$fields = "__targetEntity.*";
		
		$joins = "";
		foreach ($this->_joins as $joinDescriptor)
		{
				
				$fields .= ", \n" . implode( ",\n" , $joinDescriptor['fields'] );
				
				$joins .= "\n " . $joinDescriptor[ 'join' ];
		}
		
		// construct query
		$query = "select {$fields} from `{$table}` as __targetEntity {$joins} {$filter} ";	
		
		// print_r( $query );
				
		return $query;	
	
	}
	
	
	// releases chain
	public function yield() 
	{
	
		$query = $this->constructQuery( );
		
		
		
		// execute query and gather results
		$results = $this->qdp->execute( $query )->toArray();
		
		// handle the joined fields
		if ( count($this->_joins) > 0 )
		{
			foreach ( $results as $i => $result )
			{
				foreach ( $this->_joins as $resultingFieldName => $joinDescriptor )
				{
					$resultingField = array_pick( $result ,  $joinDescriptor['resulting_fields'] );
					
					// print_r( array( "orig" => $result ) );
					$result = array_diff_key( $result , $resultingField );
					// print_r( array( "diff" => $result ) );
					
					$result[ $resultingFieldName ] = array_combine( array_keys( $joinDescriptor['resulting_fields'] ) , $resultingField );
					
					$results[$i] = $result;
					
				}	
				
			}
		}
		
		
		// reset to old state
		$this->reset();
		
		return $results;
		
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
	
	public function update( $sourceObjectName , $entity ) 
	{
		return $this->qdp->update(
			$sourceObjectName , 
			$entity , 
			SQLFilter::Create()->setWhere(array( 'id' => $entity['id'] )) 
		);
	}
	
	public function insert( $sourceObjectName , $entity ) 
	{
		return $this->qdp->insert( $sourceObjectName , $entity );	
	}
	
	
	public function insertupdate( $sourceObjectName , $entity ) 
	{
		$this->qdp->insertupdate( $sourceObjectName , $entity );
		
		return null;
	}
	
	
	public function count( $sourceObjectName ) 
	{
		// todo: make prepared statement
		$sourceObjectName = mysql_real_escape_string( $sourceObjectName );
		return $this->qdp->execute("select count(*) c from `{$sourceObjectName}`")->toCell();
	}
	
	
	public function delete( $sourceObjectName , $entityArray ) 
	{
		return $this->qdp->delete(
			$sourceObjectName , 			
			SQLFilter::Create()->setWhere(array( 'id' => $entityArray['id'] ))
		);
	} 
	
	
	public function deleteBy( $sourceObjectName , $filterArray ) 
	{
	
		$this->reset();
		
		$this->_where = $filterArray;
		
		$queryFilter  = SQLFilter::Create();
		
		$this->createWhereClause( $queryFilter , false );
		
		return $this->qdp->delete(
			$sourceObjectName, $queryFilter
		);
		
	}
	
	
	public function getEntityField()
	{
		return new MySQLEntityField();
	}
	

	public function execute( $query )
	{
		return $this->qdp->execute( $query );
	}
	
	
	public function prepare( $query , $types )
	{
		return $this->qdp->prepare( $query , $types );
	}
	
	
	public function executeWith( )
	{
		return  call_user_func_array( array($this->qdp,'executeWith') , func_get_args() );
	}
	
	
	
	
	
	public function join( $sourceObjectName , $refDataDriver , $refObjectName , $resultingFieldName , $joinBy , $fields = null )
	{
		foreach ( $joinBy as $sourceField => $referencingField );
		
		if ( $fields == null ) 
		{
			$fields = $this->qdp->getFields($refObjectName);
		}
		
		$resulting_fields = array();
		foreach ($fields as $i=>$field)
		{
			$resulting_field = "joined__{$resultingFieldName}__{$field}";
			$fields[$i] = "`{$resultingFieldName}`.`{$field}`  as `{$resulting_field}`";
			$resulting_fields[$field] = $resulting_field;
		}
		
		$joinDescriptor = array(
		 	 "fields" => $fields
			, "resulting_fields" => $resulting_fields
			, "join" => "
				left join `{$refObjectName}` as `{$resultingFieldName}`
					on ( `__targetEntity`.`{$sourceField}` = `{$refObjectName}`.`{$referencingField}` )"
		);
		
		$this->_joins[ $resultingFieldName ] = $joinDescriptor;
		
		
		return $this;
	
	}
	
}


?>