<?


// base class for testing a model
class TestModule
{

  protected $base;

  public function __construct($base)
  {

    $this->base = $base;

    // select only methods from base class to test
    $baseclass = $base;

    $rc = new ReflectionClass($baseclass);

    $methods = $rc->getMethods(ReflectionMethod::IS_PUBLIC);

    $methods_to_test = array();

    foreach ($methods as $rm)
    {
      $method = (array) $rm;

      if ( $method["class"] == $baseclass )
      {
        $methods_to_test[] = $method["name"];
      }

    }

    $testing_methods = get_class_methods( $this );

    $hasMissingTestMethods = false;

    foreach ($methods_to_test as $method_name)
    {
      if ( $this->isMethodCandidateTest( $method_name ) )
      {
        $test_method_name = $this->generateTestMethodName( $method_name );

        if ( !in_array($test_method_name,$testing_methods))
        {
          $missing_test_methods[] = $test_method_name;

          $hasMissingTestMethods = true;
        }

      }
    }

    if ( $hasMissingTestMethods )
    {
      $this->output( "Missing test methods.\n" , "red" );

      foreach ($missing_test_methods as $test_method_name)
      {
        echo "\tpublic function {$test_method_name}() {\n\t\n\t}\n\n";
      }

      $className = get_class( $this );
      throw new Exception("Missing test methods for {$className}  !");

    }
  }

  private $assertCalled = false;

  private function isMethodCandidateTest( $methodName )
  {
    return substr( $methodName , 0 , 2 ) != '__' ;
  }

  private function generateTestMethodName( $methodName )
  {
    return "test" . strtoupper($methodName[0]).substr($methodName,1) ;
  }

  private function isTestMethod( $methodName )
  {
    return substr($methodName,0,4) == "test";
  }

  // start the entire test
  public function start()
  {
    $startTime = microtime( true );


    $candidateMethods = get_class_methods( $this );

    foreach ($candidateMethods as $method )
      if ( $this->isTestMethod( $method ) )
        $testMethods[] = $method;


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

      $this->assertCount = 0;

      $this->assertCalled = false;

      $this->output(
          "{$class}::{$method}: "
        , "yellow"
      );

      $testStartTime = microtime( true );

      call_user_func( array( $this , $method ) );

      $testRunTime = round( (microtime( true ) - $testStartTime ) * 1000 , 2 );


      if ( ! $this->assertCalled )
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
            "{$this->assertCount} asserts"
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
    $this->output( $message , "red" );
  }

  protected function assertEquality($measured,$expected, $equal = true, $message = "")
  {

    $this->assertCalled = true;
    $this->assertCount++;

    if ( ! ( ($measured == $expected) === $equal ) )
    {
      $this->outputError("Assert equality failed",array($measured,$expected));
      throw new Exception(
        'Assert Equal failed for '
        . var_export(array($measured,$expected),true)
        . $message
      );
    }

  }

  protected function assertInstance( $measured , $expected ,  $equal = true, $message = "" )
  {
    $this->assertCalled = true;
    $this->assertCount++;

    if ( !( ( $measured instanceof $expected ) === $equal ) )
    {
      $this->outputError("Assert instance failed",array($measured,$expected));
      throw new Exception(
        'Assert Equal failed for '
        . var_export(array($measured,$expected),true)
        . $message
      );
    }

  }

  protected function assertIdentity($measured,$expected, $equal = true, $message = "" )
  {

    $this->assertCalled = true;
    $this->assertCount++;

    if ( ! ( ($measured === $expected) === $equal ) )
    {
      $this->outputError("Assert identity failed",array($measured,$expected));

      throw new Exception(
        'Assert Identity failed for '
        . var_export(array($measured,$expected),true)
        . $message
      );
    }

  }

}
?>