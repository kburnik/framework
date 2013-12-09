<?

// base class for testing a model 
class TestUnitModule 
{
	
		
	public function __construct() 
	{
	
		
		
	}
	
	protected $assertCalled = false;
	protected $assertCount = 0;
	
	
	private function isTestUnitMethod( $reflectionMethod , $derivedClassName ) {
		$rm = (array) $reflectionMethod ;
		return ( $rm['class'] == $derivedClassName && substr($rm['name'],0,2) != '__' );
	}
	
	private function getTestUnitMethods() 
	{
		$derivedClassName = get_class( $this );
		
		Console::WriteLine( $derivedClassName );
		
		$rc = new ReflectionClass( $derivedClassName );
		
		$candidateMethods = $rc->getMethods(ReflectionMethod::IS_PUBLIC);
		
		foreach ( $candidateMethods as $i => $candidateMethod ) 
		{
			
			if ( ! $this->isTestUnitMethod( $candidateMethod , $derivedClassName ) ) 
			{
				unset( $candidateMethods[ $i ] ) ;
			}
		}
		
		$candidateMethods = array_values( $candidateMethods );
		
		return $candidateMethods;
	}
	
	// start the entire test
	public function start() 
	{
		$startTime = microtime( true );

		$derivedClassName = get_class( $this );
		
		$testReflectionMethods = $this->getTestUnitMethods();
		
		
		foreach ($testReflectionMethods as $method ) 			
				$testMethods[] = $method->name;
		
		$methodCount = count( $testMethods );		
		
		$methodIndex = 0;
		
		$methodsPassed = 0; 
		
		$methodsNotAsserted = 0; 
		
		$methodsCalled = 0;
		
		$class = get_class( $this );
		
		if ( ! is_array( $testMethods ) ) 
		{
		
			$this->outputError("No methods to test for $class\n" , "yellow");
			
			return;
		}
		
		foreach ($testMethods as $method) 
		{				
			
			$methodIndex++;
			
			
			
			$this->output(
				  "{$class}::{$method}: " 
				, "yellow" 
			);
			
			$testStartTime = microtime( true );
			
			$testUnitObject = new $derivedClassName();
			
			
			call_user_func( array( $testUnitObject , $method ) );
			
			
			
			$testRunTime = round( (microtime( true ) - $testStartTime ) * 1000 , 2 );
			
			
			if ( ! $testUnitObject->assertCalled ) 
			{
				
				$methodsNotAsserted++;
				
				$this->output(
					  "NO ASSERT CALLED {$methodsNotAsserted} / {$methodCount}: {$class}::{$method}\n" 
					, "red"
				);
				
			} 
			else 
			{
			
				$methodsPassed++;
				
				$this->output(
					   $testUnitObject->assertCount . " asserts"
					, "light_gray"
				);
				
				$this->output(
					  " {$testRunTime} ms"
					, "cyan" 
				);
				
				$this->output(
					  " SUCCESS {$methodsPassed} / {$methodCount}\n"
					, "green" 
				);
				
			}
			
			// destroy the object
			unset( $testUnitObject );
			
			$methodsCalled++;	
			
		}
		
		if ( $methodsNotAsserted == 0 ) 
		{		
			$summary_color = "green";			
		}
		else 
		{		
			$summary_color = "red";
		}
		
		$totalRunTime = round( (microtime( true ) - $startTime) * 1000 , 2 );
		
		$this->output(
		
			    "[ Test results for {$class} : "
			  . "{$methodsPassed} PASSED | "
			  . "{$methodsNotAsserted} UNTESTED | "
			  . "{$methodsCalled} TOTAL | "
			  . "Runtime: {$totalRunTime} ms ]\n"
			  
			, $summary_color
			
		);
		
	}
	
	private function output( $message , $color = "yellow" ) 
	{
		echo ShellColors::getInstance()->getColoredString( $message , $color );
	}
	
	private function outputError( $message , $data ) 
	{
		$out = "$message\nData: ".var_export( $data , true);
		$this->output( $out , "red" );
	}
	
	protected function assertEqual($expected, $measured, $message = "") 
	{
		
		$this->assertCalled = true;
		$this->assertCount++;
		
		if ( ! ($measured == $expected) ) 
		{
			$outputArray = array( "expected" => $expected, "measured" => $measured);
			$this->outputError("Assert equality failed",$outputArray);			
			throw new Exception(
				'Assert Equal failed for ' 
				. var_export($outputArray,true) 
				. $message
			);
		}
		
	}
	
	protected function assertIdentical($expected, $measured, $message = "" ) 
	{
		
		$this->assertCalled = true;
		$this->assertCount++;
		
		if ( ! ($measured === $expected) ) 
		{
		
			$outputArray = array( "expected" => $expected, "measured" => $measured);
			$this->outputError("Assert identity failed on ", $outputArray );
			
			throw new Exception(
				'Assert Identity failed for ' 
				. var_export( $outputArray ,true) 
				. $message
			);
		}
		
	}

}
?>