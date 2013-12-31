<?

class TestCoverage 
{


	public function addCoverageCalls( $phpCode )
	{
	
		$tokens = token_get_all( $phpCode );
		
		
		$count = 0;
		
		$skip = false;
		
		$inInterface = false;		
		$inInterfaceBody = false;
		$interfaceCurlyLevel = 0;

		$registerTagSet = false;
		
		$inAbstractDefinition = false;
		
		$inFor = false;
		$inForHeader = false;
		
		$parenLevel = 0;

		foreach ($tokens as $token) 
		{
		   if (is_string($token))
		   {
		   
			   // simple 1-character token
			   $out .= $token;
			   
			   
			   if ( $token == '{' ) 
			   {
			   
				
					if ( $inInterface ) 
					{
						$inInterfaceBody = true;
						
						// echo "In interface body\n";
						
						$inInterface = false;
						
						$interfaceCurlyLevel = 1;
					
					} 
					else if ( $inInterfaceBody ) 
					{
						$interfaceCurlyLevel++;
						// echo "In interface body still\n";
					}
					
					
					
			   
			   } 
			   else if ( $token == '}' ) 
			   {
			   
					// echo "Curly closed\n";
					if ( $inInterfaceBody )
					{
						$interfaceCurlyLevel--;
						
						if ( $interfaceCurlyLevel == 0 ) {
							$inInterfaceBody = false;
						}
					
					}
			   
			   }
			   else if ( $token == '(' ) 
			   {
					
					
					
					if ( $inFor ) 
					{
						$inForHeader = true;
						$inFor = false;
						
						$forParenLevel = $parenLevel;
						
					}
					
					$parenLevel++;
					
			   
			   } 
			   else if ( $token == ')' ) 
			   {
					
					$parenLevel--;
					
					if ( $inForHeader && $parenLevel == $forParenLevel ) 
					{
						$inForHeader = false;
					
					}
			   }
			   
			    
			   
			   $skip = ( $inInterfaceBody || $inAbstractDefinition || $inForHeader );
			   
			   if ( $token == ';' )
			   {
					if ( ! $skip )
					{
						$out.="/*<TestCoverage>*/TestCoverage::Cover(__FILE__,__LINE__,$count);/*</TestCoverage>*/";
						$count++;
					
					}
					
					
					if ( $inAbstractDefinition )
					{
						$inAbstractDefinition = false;
					}
					
					
			   }
			   
			  
		   } 
		   else
		   {
			
			   // token array
			   list($id, $text,$other) = $token;
			   
			   
			   
			   if ( $id == T_OPEN_TAG && ! $registerTagSet )
			   {
			   
					$firstOpenTag = $text;
					
					$registerTagSet = true;
					
					continue;
			   }
			   
			   if ( $id == T_ABSTRACT )
			   {
					// echo "Abstract!\n";

					$inAbstractDefinition = true;
			   }
			   
			   
			   $out.=$text;
			   
			   
			   
			   
			   
			   if ( $id == T_INTERFACE )
			   {
					$inInterface = true;					
					
			   }
			   
			   
			   if ( $id == T_FOR ) 
			   {
					$inFor = true;
			   
			   }
			   
			   
			   // echo "___ $id " . token_name($id) ." $text $other \n";
			   
			   
			   
			   
		   }
		}
		
		if ( $count > 0 )
				$firstOpenTag.="/*<TestCoverage>*/include_once('".__FILE__."'); TestCoverage::RegisterFile(__FILE__,$count);/*</TestCoverage>*/";
					
					
		return  $firstOpenTag . $out;
	}
	
	public function removeCoverageCalls( $phpCode )
	{
	
		$tagStart = '\/\*\<TestCoverage\>\*\/';
		$tagEnd = '\/\*\<\/TestCoverage\>\*\/';
		
		
		// (.*?) is ungreedy !! weeeee
		$pattern = '/('.$tagStart.')(.*?)+('.$tagEnd.')/ui';
		
		$phpCode = preg_replace($pattern,'',$phpCode);
		
		return $phpCode;
	
	}
	
	
	protected static $coverage = array();
	
	public static function RegisterFile( $filename , $count )
	{
		self::$coverage[ $file ][ 'count' ] = $count;
	}
	
	
	public static function Cover( $filename, $line, $index , $count ) 
	{
		self::$coverage[ $file ][ 'covered' ][ $index ] = true;
	
	}
	
	
	public static function addCoverageCallsToFile( $file ) 
	{
		
		// omit self
		if ( realpath(__FILE__) === realpath( $file ) )
			return;
		
		$code = file_get_contents( $file );
		
		$code = self::removeCoverageCalls( $code );
		
		$coveredCode = self::addCoverageCalls( $code );
		
		file_put_contents( $file, $coveredCode );
	
	}
	
	
	public static function removeCoverageCallsFromFile( $file ) 
	{
	
		// omit self
		if ( realpath(__FILE__) === realpath( $file ) )
			return;
		
		$code = file_get_contents( $file );
		
		$clearCode = self::removeCoverageCalls( $code );		
		
		file_put_contents( $file, $clearCode );
	
	}

	
	public static function ShowResults()
	{
		
		foreach ( self::$coverage as $file => $res ) 
		{
			$count = $res[ 'count' ];
			$covered = $res[ 'covered' ];
			echo "$file: $covered / $count\r\n";
		}
	
	}


}




?>