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
	
	protected function operatorBetween( $entity , $params ) 
	{

		list( $field, $from , $to ) = $params;

		$isBetween =
			( 
				$entity[ $field ] >= $from 
				&& 
				$entity[ $field ] <= $to 
			);
		
		return $isBetween;
	
	}
	
	protected function operatorEq( $entity , $params )
	{
	
		list( $field, $val ) = $params;
		
		$isEq = ( $entity[ $field ] == $val );
		
		return $isEq;		
	}
	
	protected function operatorNe( $entity , $params )
	{
	
		list( $field, $val ) = $params;
		
		$isNe = ( $entity[ $field ] != $val );
		
		return $isNe;		
	}
	
	
	protected function operatorGt( $entity , $params )
	{
	
		list( $field, $val ) = $params;
		
		$isGt = ( $entity[ $field ] > $val );
		
		return $isGt;		
	}
	
	
	protected function operatorLt( $entity , $params )
	{
	
		list( $field, $val ) = $params;
		
		$isLt = ( $entity[ $field ] < $val );
		
		return $isLt;		
	}
	
	
	protected function operatorGtEq( $entity , $params ) 
	{
	
		list( $field, $val ) = $params;
		
		$isGtEq = ( $entity[ $field ] >= $val );
		
		return $isGtEq;		
	}
	
	
	protected function operatorLtEq( $entity , $params )
	{
	
		list( $field, $val ) = $params;
		
		$isLtEq = ( $entity[ $field ] <= $val );
		
		return $isLtEq;
	}
	
	
	protected function operatorIn( $entity , $params )
	{
	
		list( $field, $values ) = $params;
		
		$isIn = ( in_array( $entity[ $field ] , $values ));
		
		return $isIn;
	}
	
	protected function operatorNin( $entity , $params )
	{
	
		list( $field, $values ) = $params;
		
		$isNin = ! ( in_array( $entity[ $field ] , $values ));
		
		return $isNin;
	}
	
	
	public function matches( $entity ) 
	{
		
		$filter = $this->filter;
		
		
		// special operators like :between :gt :lt, etc.
		foreach ( $filter as $key => $val )
		{
		
			if ( $key[0] == ':' ) 
			{
				$operatorName = substr($key,1);
				
				$operatorMethodName = "operator{$operatorName}";
				
				$res = $this->$operatorMethodName( $entity , $val );
				
				if ( !$res ) 
					return false;
				
				unset( $filter[ $key ] );
			}
			
		}
		
		
		// regex match for 'like' clause, it's "and" clause for all fields
		$likeMatch = false;
		
		
		// assume all matches, trying to not match
		$fieldsMatch = true;
		foreach ( $filter as $fieldName => $value )
		{
			
			if ( is_array( $value ) )
			{
				$likeMatch = true;
				
				$pattern = reset( $value );
				$pattern = array_map( 'preg_quote' , explode('%',$pattern) );
				$regexPattern =  '/^' . implode('(.*?)',$pattern) . '$/i';
				
				if ( ! preg_match( $regexPattern , $entity[ $fieldName ] ) )
				{
					$fieldsMatch = false;
					break;
				}
			}		
		}
		
		
		// default exact matching
		if ( ! $likeMatch )
			$fieldsMatch = ( $filter == array_intersect_assoc ( (array) $entity , $filter )  );
		
		return $fieldsMatch;
	}



}



?>