<?

class TestCoverage 
{


	public function addCoverageCalls( $phpCode )
	{
	
		$tokens = token_get_all( $phpCode );
		
		
		$count = 0;
		foreach ($tokens as $token)
		{
			if ( $token === ';' ) {
				$count++;
			}
		
		}
		
		$index = 1;

		foreach ($tokens as $token) 
		{
		   if (is_string($token))
		   {
		   
			   // simple 1-character token
			   $out .= $token;
			   
			   if ( $token == ';' )
			   {
					$out.="/*<TestCoverage>*/TestCoverage::Cover(__FILE__,__LINE__,$index);/*</TestCoverage>*/";
					$index++;
			   }
		   } 
		   else
		   {
			
			   // token array
			   list($id, $text) = $token;
			   
			   $out.=$text;
			   
			   if ( $id == T_OPEN_TAG)
			   {
					$out.="/*<TestCoverage>*/include_once('".__FILE__."'); TestCoverage::RegisterFile(__FILE__,$count);/*</TestCoverage>*/";
			   }
		   }
		}
	
		return $out;
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
	
	
	protected static $coverage;
	
	public static function RegisterFile( $filename , $count )
	{
		self::$coverage[ $file ][ 'count' ] = $count;
	}
	
	
	public static function Cover( $filename, $line, $index , $count ) 
	{
		self::$coverage[ $file ][ 'covered' ][] = $index;
	
	}



}




?>