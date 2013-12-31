<?
class SampleClass extends Base 
{

	public function __construct( $values )
	{
		$this->values = $values;
	}
	
}


function outputTrue()
{
	echo "A is 1";
}

function outputFalse()
{
	echo "A is not 1";

}

/*
	sample multiline commment
*/

echo "Hello";

$a = 2/2;


// sample single line comment

if ( $a == 1 ) 
{
	
	outputTrue();

} 
else 
{
	outputFalse();

}

echo "done";

?>