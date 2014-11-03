<?

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
    $this->doSomething();

  }

  protected function doSomething()
  {
    echo "Doing something";
  }

}

class SampleClass extends SomeAbstraction implements SomeInterface
{

  private $values;

  public function __construct( $values )
  {
    $this->values = $values;
  }

  public function someMethod( $first , $second )
  {
    $a = $first;
    $b = $second;

    $this->values = array( $a, $b );
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

if ( true )
  echo "Hello!";

?>