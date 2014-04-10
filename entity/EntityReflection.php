<?


class EntityReflection 
{

	private $entityClassName , $reflectionClass , $dataDriver ;

	public function __construct( $entityClassName , $dataDriver ) 
	{
	
		if ( !class_exists( $entityClassName ) )
		{
			throw new Exception( "Class not found: $entityClassName" );
		}
		
		// try to reflect
		$this->reflectionClass = new ReflectionClass( $entityClassName );
		
		if ( ! $this->reflectionClass->isSubclassOf('Entity') )
		{
			throw new Exception( "Not instance of Entity: $entityClassName" );
		}
		
		
		$this->dataDriver = $dataDriver;
		
		$this->entityClassName = $entityClassName;
		
		
		
	}


	public function getFields() 
	{
		$properties = $this->reflectionClass->getProperties( ReflectionProperty::IS_PUBLIC );
		
		$out = array();
		
		foreach  ( $properties as $prop )
		{
			$out[] = $prop->name;
		}
		
		return $out;
	}
	
	private function parseDocComment( $doc )
	{
	
		$doc = str_replace('/**','',$doc);
		$doc = str_replace('*/','',$doc);
		$doc = trim( $doc );
		
		$lines = explode("\n",$doc);
		
		$out = array();
		
		foreach ( $lines as $line )
		{
		
			// echo $line."\n";
			$code ="<?". trim($line)."?>";
			$tokens = token_get_all ($code);
			array_pop($tokens);
			array_shift($tokens);
			foreach ($tokens as $i=>$token)
			{
				if (is_array($token))
				{
					$tokens[$i][0] = token_name($token[0]);
				}
			}
			
			$functionFound = false;
			$leftParen = false;
			$rightParen = false;
			$func = null;
			$args = array();
			
			foreach ( $tokens as $token )
			{
				if ( is_array( $token ) )
				{
					// print_r( $token );
					if ( !$functionFound && $token[0] == 'T_STRING' ) 
					{
						$func = $token[1];
						$functionFound = true;
						continue;
					}
					
					if ($leftParen)
					{
						$args[] = $token[1];
					}
				} 
				else if ( $token == '(' )
				{
					$leftParen  = true;
				} else if ( $token == ')' )
				{
					$out[] = array( $func , $args );
					$rightParen = true;
					$leftParen = false;
					$functionFound = false;					
					$func = null;
					$args = array();
				}
			
			}
			
			
			
		
		}
		
		return $out;
	
	}
	
	private function applyDocComment( $comment , $entityField )
	{
	
		
		
		if ( ! ( $calls =  $this->parseDocComment( $comment ) ) ) 		
			return false;		
		
		
		$entityField->reset();
			
		foreach( $calls as $call ) 
		{
			list($func,$args) = $call;
			
			
			if ( !method_exists( $entityField , $func ) )
			return false;
			
			call_user_func_array( array($entityField , $func) , $args );
			
		}
		
		return true;
	}
	
	public function isDatabaseReady( )
	{
	
		$properties = $this->reflectionClass->getProperties( ReflectionProperty::IS_PUBLIC );
		
		$entityField = $this->dataDriver->getEntityField();
		
		
		$structure = array();
		
		foreach ( $this->getFields() as $field ) 
		{
			$reflectionProp = new ReflectionProperty($this->entityClassName, $field);
			
			$comment = $reflectionProp->getDocComment();
						
			if ( ! $this->applyDocComment( $comment , $entityField ) )
			{
			
				return false;
			}
			
		}
		
	
		return true;
	
	}
	
	
	public function getPrimaryKey()
	{
	
		$entityField = $this->dataDriver->getEntityField();
		
		
		foreach ( $this->getFields() as $field ) 
		{
			$reflectionProp = new ReflectionProperty($this->entityClassName, $field);
			$comment = $reflectionProp->getDocComment();
			
			if ( ! $this->applyDocComment( $comment , $entityField ) )
			{
				continue;
			}
			
			if ($entityField->isPrimaryKey())
				return $field;
			
		}
		
		return null;
	}
	
	public function getStructure( )
	{
		
		$structure = array();
		
		$entityField = $this->dataDriver->getEntityField();
		
		
		foreach ( $this->getFields() as $field ) 
		{
			$reflectionProp = new ReflectionProperty($this->entityClassName, $field);
			
			$comment = $reflectionProp->getDocComment();
			
			if ( ! $this->applyDocComment( $comment , $entityField ) )
			{
				return null;
			}
			
			$structure[ $field ] = $entityField->yield();
			
		}
		
		return $structure;
	}
	
}

?>