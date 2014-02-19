<?

class TestCoverage 
{

	private $count = 0 ;
	private function resetCounter() {	
		$this->count = 0;
	}
	
	private function getNextCoverageCall() 
	{
		$code = "/*<TestCoverage>*/TestCoverage::Cover(__FILE__,__LINE__,{$this->count});/*</TestCoverage>*/";
		$this->count++;
		return $code;
	}
	
	private function wrapInCurlies( $code , $startPosition )
	{
		$openedCurly = "/*<TestCoverage>*/{/*</TestCoverage>*/";
		$closedCurly = "/*<TestCoverage>*/}/*</TestCoverage>*/";
		
		$code = 
			substr( $code , 0 , $startPosition + 1  ) 
			. $openedCurly 
			. substr($code,$startPosition+1) 
			. $this->getNextCoverageCall() 
			. $closedCurly
		;
		
		
		return $code;
		
	}

	public function addCoverageCalls( $phpCode )
	{
		$this->resetCounter();
	
		$tokens = token_get_all( $phpCode );
				
		
		$skip = false;
		
		$inInterface = false;		
		$inInterfaceBody = false;
		$interfaceCurlyLevel = 0;

		$registerTagSet = false;
		
		$inAbstractDefinition = false;
		
		$inFor = false;
		$inForHeader = false;
		
		$parenLevel = 0;
		
		
		$inReturnStatement = false;

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
					else if ( $inBlockNakedBody ) 
					{
						$inBlockNakedBody = false;
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
					
					if ( $inBlock )
					{
						$inBlockHeader = true;
						$inBlock = false;
						
						$blockParenLevel = $parenLevel;
					}
					
					
					$parenLevel++;
					
			   
			   } 
			   else if ( $token == ')' )
			   {
					
					$parenLevel--;
					
					if ( $inBlockHeader && $parenLevel == $blockParenLevel ) 
					{
						$inBlockHeader = false;
						$inBlockNakedBody = true;
						$blockBodyStartPosition = strlen( $out );						
					}
					
			   }
			   
			    
			   
			   $skip = ( $inInterfaceBody || $inAbstractDefinition || $inBlockHeader || $inReturnStatement );
			   
			   if ( $token == ';' )
			   {
					
					if ( $inBlockNakedBody )
					{
						$out = $this->wrapInCurlies( $out , $blockBodyStartPosition );
						continue;
					}
					
					if ( ! $skip )
					{
						$out .= $this->getNextCoverageCall();
					}
					
					
					if ( $inAbstractDefinition )
					{
						$inAbstractDefinition = false;
					}
					
					if ( $inReturnStatement )
					{
						$inReturnStatement = false;
					}
					
					
			   }
			   
			  
		   } 
		   else
		   {
			
				// token array
				list( $id, $text, $other ) = $token;

				// insert before the return token
				if ( $text == "return" )
				{
					$inReturnStatement = true;			
					$out .= $this->getNextCoverageCall();
				}
			   
			   
			   if ( $id == T_OPEN_TAG && ! $registerTagSet )
			   {
			   
					$firstOpenTag = $text;
					
					$registerTagSet = true;
					
					continue;
			   }
			   
			   if ( $id == T_ABSTRACT )
			   {
					$inAbstractDefinition = true;
			   }
			   
			   if ( $id == T_INTERFACE )
			   {
					$inInterface = true;
			   }
			   
			   if ( in_array( $id , array( T_IF , T_WHILE , T_FOR, T_FOREACH ) ) )
			   {
					$inBlock = true;
			   }
			   
			   
			   $out .= $text;
			   			   
			   			   
			   // echo "___ $id " . token_name($id) ." $text $other \n";
			   
			   
		   }
		}
		
		if ( $this->count > 0 )
			$firstOpenTag .= "/*<TestCoverage>*/include_once('".__FILE__."'); TestCoverage::RegisterFile(__FILE__,{$this->count});/*</TestCoverage>*/";
					
					
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
			return ;
		
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