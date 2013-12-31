<?



class TestCoverageTestCase extends TestCase
{


	protected $coverage;


	public function __construct()
	{
	
		$this->coverage = new TestCoverage();
	
	
	}
	
	
	public function addCoverageCalls_simpleFunctionCall_addsCoverCalls()
	{
	
		$code = '<? myfunc("hello worlds"); ?>';
		
		$expectedCode= '<?/*<TestCoverage>*/include_once(\'/home/eval/framework/base/TestCoverage.php\'); TestCoverage::RegisterFile(__FILE__,1);/*</TestCoverage>*/ myfunc("hello worlds");/*<TestCoverage>*/TestCoverage::Cover(__FILE__,__LINE__,0);/*</TestCoverage>*/ ?>';
		
		$coveredCode = $this->coverage->addCoverageCalls( $code );
		
		$this->assertEqual( $expectedCode , $coveredCode );
	
	}
	
	
	public function addCoverageCalls_forLoopWithBlockOfCode_addsCoverCallsOnlyToBody()
	{
	
		$code = '<? for( $i=0; $i < 5; $i++ ) { echo $i; }?>';
		
		$expectedCode= '<?/*<TestCoverage>*/include_once(\'/home/eval/framework/base/TestCoverage.php\'); TestCoverage::RegisterFile(__FILE__,1);/*</TestCoverage>*/ for( $i=0; $i < 5; $i++ ) { echo $i;/*<TestCoverage>*/TestCoverage::Cover(__FILE__,__LINE__,0);/*</TestCoverage>*/ }?>';
		
		$coveredCode = $this->coverage->addCoverageCalls( $code );
		
		$this->assertEqual( $expectedCode , $coveredCode );
	
	}
	
	
	public function addCoverageCalls_forLoopWithParensInConditionAndBlockOfCode_addsCoverCallsOnlyToBody()
	{
	
		$code = '<? for( $i=0; ($i < (5)) ; $i++ ) { echo $i; }?>';
		
		$expectedCode= '<?/*<TestCoverage>*/include_once(\'/home/eval/framework/base/TestCoverage.php\'); TestCoverage::RegisterFile(__FILE__,1);/*</TestCoverage>*/ for( $i=0; ($i < (5)) ; $i++ ) { echo $i;/*<TestCoverage>*/TestCoverage::Cover(__FILE__,__LINE__,0);/*</TestCoverage>*/ }?>';
		
		$coveredCode = $this->coverage->addCoverageCalls( $code );
		
		$this->assertEqual( $expectedCode , $coveredCode );
	
	}
		
	
	private function __addRemoveAssertKeepsCodeUntouched( $code )
	{
		$coveredCode = $this->coverage->addCoverageCalls( $code );
		
		$clearCode = $this->coverage->removeCoverageCalls( $coveredCode );
		
		$this->assertEqual( $code , $clearCode );
	
	}
	
	
	private function __assertKeepsCodeUntouched( $code )
	{
		$coveredCode = $this->coverage->addCoverageCalls( $code );
		
		$this->assertEqual( $code , $coveredCode );
	
	}
	
	public function addCovergeCalls_toInterface_keepsCodeUntouched() 
	{
		
		$code = '<?
			interface SampleInterface 
			{
				
				public function foo();
				
				public function bar();
				
			}
		?>';
		
		$this->__assertKeepsCodeUntouched( $code );
	
	}
	
	
	public function addCoverageCalls_toAbstractMethods_keepsCodeUnTouched() 
	{
		$code = '<?
			class SampleClassWithAbstractMethods
			{
				
				public abstract function foo();
				
				public abstract function bar();
				
			}
		?>';
		
		$this->__assertKeepsCodeUntouched( $code );
	
	}
	
	
	public function addCoverageCalls_toEmptyMethods_keepsCodeUnTouched() 
	{
		$code = '<?
			class SampleClassWithEmptyMethods
			{
				
				public abstract function foo()
				{
				
				
				}
				
				public abstract function bar()
				{
				
				}
				
			}
		?>';
		
		$this->__assertKeepsCodeUntouched( $code );
	
	}
	
	
	
	public function addCoverageCalls_toFileWithMultiplePHPtags_putsStartingCoverageOnlyAtFirstTag() 
	{
		$code = '<? ?> some text to output <? echo "hi"; ?>';
		
		$expectedCode = '<?/*<TestCoverage>*/include_once(\'/home/eval/framework/base/TestCoverage.php\'); TestCoverage::RegisterFile(__FILE__,1);/*</TestCoverage>*/ ?> some text to output <? echo "hi";/*<TestCoverage>*/TestCoverage::Cover(__FILE__,__LINE__,0);/*</TestCoverage>*/ ?>';
		
		$coveredCode = $this->coverage->addCoverageCalls( $code );
		
		$this->assertEqual( $expectedCode , $coveredCode );
	
	}
	
	
	public function addCovergeCalls_sampleScript1_adheresTosample1Out() 
	{
		
		$code = file_get_contents( dirname(__FILE__) .'/samples/sample1.php' );
		
		$coveredCode = $this->coverage->addCoverageCalls( $code );
		
		$expectedCode = file_get_contents( dirname(__FILE__) .'/samples/sample1.out.php' );
		
		$this->assertEqual( $expectedCode , $coveredCode );
	
	}
	
	
	public function addThenRemoveCovergeCalls_simpleFunctionCall_keepsCodeUntouched()
	{
	
		$code = '<? myfunc("hello worlds"); ?>';
		
		$this->__addRemoveAssertKeepsCodeUntouched( $code );
	
	}
		
	public function addThenRemoveCovergeCalls_sampleScript1_keepsCodeUntouched() 
	{
		
		$code = file_get_contents( dirname(__FILE__) .'/samples/sample1.php' );		
		
		$this->__addRemoveAssertKeepsCodeUntouched( $code );
	
	}
	


}



?>