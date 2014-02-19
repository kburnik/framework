<?

class TestCoverage 
{

	private static $count = 0 ;
	private function resetCounter()
	{
		self::$count = 0;
	}
	
	private function getNextCoverageCall()
	{
		$code = "/*<TestCoverage>*/TestCoverage::Cover(__FILE__,__LINE__,".self::$count.");/*</TestCoverage>*/";
		self::$count++;
		return $code;
	}
	
	private function wrapInCurlies( $code , $startPosition , $addition )
	{
		$openedCurly = "/*<TestCoverage>*/{/*</TestCoverage>*/";
		$closedCurly = "/*<TestCoverage>*/}/*</TestCoverage>*/";
		
		$code = 
			substr( $code , 0 , $startPosition + 1  ) 
			. $openedCurly 
			. substr($code,$startPosition+1) 
			. $addition 
			. $closedCurly
		;
		
		
		return $code;
		
	}

	public function addCoverageCalls( $phpCode )
	{
		self::resetCounter();
	
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
			   
					if ( $inClass )
					{
						$inClassBody = true;
						
						$inClass = false;
						
						$classCurlyLevel = 1;
						
					}
					else if ( $inClassBody )
					{
						$classCurlyLevel++;
					}
				
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
					
					
					if ( $inFunction )
					{
						$inFunctionBody = true;
						
						$inFunction = false;
						
						$functionCurlyLevel = 1;
						
					}
					else if ( $inFunctionBody )
					{
						$functionCurlyLevel++;
					}
					
					
					
					if ( $inBlockNakedBody ) 
					{
						$inBlockNakedBody = false;
					}
					
					
					
			   
			   } 
			   else if ( $token == '}' )
			   {
			   
					// echo "Curly closed\n";
					if ( $inClassBody )
					{
						$classCurlyLevel--;
						
						if ( $classCurlyLevel == 0 )
						{
							$inClassBody = false;
						}
						
					}
					
					if ( $inInterfaceBody )
					{
						$interfaceCurlyLevel--;
						
						if ( $interfaceCurlyLevel == 0 ) {
							$inInterfaceBody = false;
						}
					
					}
					
					if ( $inFunctionBody )
					{
						$functionCurlyLevel--;
						
						if ( $functionCurlyLevel == 0 )
						{
							$inFunctionBody = false;
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
			   
			    
			   // skip the coverage after the semicolon
			   $skip = ( 
					( $inClassBody && !$inFunctionBody )
					|| $inInterfaceBody 
					|| $inAbstractDefinition 
					|| $inBlockHeader 
					|| $inReturnStatement 
				);
			   
			   if ( $token == ';' )
			   {
					
					if ( $inBlockNakedBody )
					{
						$inBlockNakedBody = false;
						$coverageCode = "";
						
						// maybe have to skip adding code because of return statement
						if ( !$skip )
							$coverageCode = self::getNextCoverageCall();
							
						$out = self::wrapInCurlies( $out , $blockBodyStartPosition , $coverageCode );
						continue;
					}
					
					if ( ! $skip )
					{
						$out .= self::getNextCoverageCall();
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
				if ( $id == T_RETURN )
				{
					$inReturnStatement = true;			
					$out .= self::getNextCoverageCall();
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
			   
			   if ( $id == T_CLASS )
			   {
					$inClass = true;
			   }
			   
			   if ( $id == T_INTERFACE )
			   {
					$inInterface = true;
			   }
			   
			   if ( $id == T_FUNCTION  )
			   {
					$inFunction = true;
			   }
			   
			   if ( in_array( $id , array( T_IF , T_ELSEIF , T_WHILE , T_FOR, T_FOREACH ) ) )
			   {
					$inBlock = true;
			   }
			   
			   // after else assume in naked body, the curly opened brace will reset this state
			   if ( $id == T_ELSE )
			   {
					$inBlockNakedBody = true;
					$blockBodyStartPosition = strlen( $out ) + strlen( $text );
			   }
			   
			   
			   $out .= $text;
			   			   
			   			   
			   // echo "___ $id " . token_name($id) ." $text $other \n";
			   
			   
		   }
		}
		
		if ( self::$count > 0 )
			$firstOpenTag .= "/*<TestCoverage>*/include_once('".__FILE__."'); TestCoverage::RegisterFile(__FILE__,".self::$count.");/*</TestCoverage>*/";
					
					
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
	
	
	public static function Cover( $filename, $line, $index ) 
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