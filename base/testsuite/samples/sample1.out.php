<?/*<TestCoverage>*/include_once('/home/eval/framework/base/TestCoverage.php'); TestCoverage::RegisterFile(__FILE__,14);/*</TestCoverage>*/

interface SomeInterface 
{

	public function someMethod( $first , $second ) ;

}


abstract class SomeAbstraction 
{
	
	public abstract function someOtherAbstractMethod( $arg1, $arg2, $arg3 );
	
	/*
		;
		;
		;
	*/
	
	public function someNonAbstractMethod()
	{
		$this->doSomething();/*<TestCoverage>*/TestCoverage::Cover(__FILE__,__LINE__,0);/*</TestCoverage>*/
	
	}
	
	protected doSomething()
	{
		echo "Doing something";/*<TestCoverage>*/TestCoverage::Cover(__FILE__,__LINE__,1);/*</TestCoverage>*/	
	}

}

class SampleClass extends SomeAbstraction implements SomeInterface 
{

	private $values;/*<TestCoverage>*/TestCoverage::Cover(__FILE__,__LINE__,2);/*</TestCoverage>*/

	public function __construct( $values )
	{
		$this->values = $values;/*<TestCoverage>*/TestCoverage::Cover(__FILE__,__LINE__,3);/*</TestCoverage>*/
	}
	
	public function someMethod( $first , $second ) 
	{
		$a = $first;/*<TestCoverage>*/TestCoverage::Cover(__FILE__,__LINE__,4);/*</TestCoverage>*/
		$b = $second;/*<TestCoverage>*/TestCoverage::Cover(__FILE__,__LINE__,5);/*</TestCoverage>*/
		
		$this->values = array( $a, $b );/*<TestCoverage>*/TestCoverage::Cover(__FILE__,__LINE__,6);/*</TestCoverage>*/
	}
	
}


function outputTrue()
{
	echo "A is 1";/*<TestCoverage>*/TestCoverage::Cover(__FILE__,__LINE__,7);/*</TestCoverage>*/
}

function outputFalse()
{
	echo "A is not 1";/*<TestCoverage>*/TestCoverage::Cover(__FILE__,__LINE__,8);/*</TestCoverage>*/

}

/*
	sample multiline commment
*/

echo "Hello";/*<TestCoverage>*/TestCoverage::Cover(__FILE__,__LINE__,9);/*</TestCoverage>*/

$a = 2/2;/*<TestCoverage>*/TestCoverage::Cover(__FILE__,__LINE__,10);/*</TestCoverage>*/


// sample single line comment

if ( $a == 1 ) 
{
	
	outputTrue();/*<TestCoverage>*/TestCoverage::Cover(__FILE__,__LINE__,11);/*</TestCoverage>*/

} 
else 
{
	outputFalse();/*<TestCoverage>*/TestCoverage::Cover(__FILE__,__LINE__,12);/*</TestCoverage>*/

}

echo "done";/*<TestCoverage>*/TestCoverage::Cover(__FILE__,__LINE__,13);/*</TestCoverage>*/

?>