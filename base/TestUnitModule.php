<?

class AssertException extends Exception {

}

// base class for testing a model
class TestUnitModule
{


  public function __construct() {}

  protected $assertCalled = false;
  protected $assertCount = 0;



  // for derived classes to specify from which classes to inherit the testing methods
  public static $inherits = array();
  //

  private function isTestUnitMethod( $reflectionMethod , $derivedClassName )
  {
    $rm = (array) $reflectionMethod ;


    $methodOwnerOk = false;

    if ( is_array( $derivedClassName::$inherits ) )
    {
      $methodOwnerOk = in_array($rm['class'] , $derivedClassName::$inherits  );
    }

    $methodOwnerOk = $methodOwnerOk || ( $rm['class'] == $derivedClassName );


    return ( $methodOwnerOk && substr($rm['name'],0,2) != '__' );
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
  public function start($filter = null)
  {
    echo "Using filter: $filter\n";
    $startTime = microtime( true );

    $derivedClassName = get_class( $this );

    $testReflectionMethods = $this->getTestUnitMethods();


    foreach ($testReflectionMethods as $method )
    {
      if ($filter != null && !preg_match("/$filter/u", $method))
        continue;

      $testMethods[] = $method->name;
    }

    $methodCount = count( $testMethods );

    $methodIndex = 0;

    $methodsPassed = 0;

    $methodsNotAsserted = 0;

    $methodsCalled = 0;

    $class = get_class( $this );

    if ( ! is_array( $testMethods ) )
    {

      $this->outputError("No methods to test for $class\n" , null);

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


      try  {
        call_user_func( array( $testUnitObject , $method ) );
      } catch (AssertException $ex) {
        $methodsPassed--;
      }

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

  private function outputError( $message )
  {
    $this->output($message . "\n" , "red");
  }

  private function showDiff($expected, $measured)
  {
    $temp_dir = sys_get_temp_dir();
    $temp_expected = tempnam($temp_dir, "expected_");
    $temp_measured = tempnam($temp_dir, "measured_");
    file_put_contents($temp_expected, var_export($expected, true)."\n");
    file_put_contents($temp_measured, var_export($measured, true)."\n");
    echo `git diff --color --no-index $temp_expected $temp_measured`;
    unlink($temp_expected);
    unlink($temp_measured);
  }

  protected function assertEqual($expected, $measured, $message = "")
  {

    $this->assertCalled = true;
    $this->assertCount++;

    if ( ! ($measured == $expected) )
    {
      $this->outputError("Assert failed. Diff is displayed below.");
      $this->showDiff($expected, $measured);

      throw new AssertException(
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
      $this->outputError("Assert failed. Diff is displayed below.");
      $this->showDiff($expected, $measured);

      throw new AssertException(
        'Assert Identity failed for '
        . var_export( $outputArray ,true)
        . $message
      );
    }

  }

  public static function runAllTestsOnTestModule($mixedModule, $filter = null)
  {

    $basename = basename( $mixedModule );

    $class = str_replace( '.php' , '' , $basename);

    if ( class_exists( $class ) )
    {
      $testUnitModule = new $class(  );
      $testUnitModule->start($filter);
    }
    else
    {
      throw new Exception( "Class does not exist : $class" );
    }

  }


  // args can be empty, list of filenames or list of classes to test
  public static function run( $args , $filter = null )
  {

    if (!defined('SHELL_MODE'))
    {
      ob_end_flush();
      ob_flush();
      flush();

      define('SHELL_MODE',true);
    }


    if (count($args) > 0 )
    {
      $testModuleIdentifiers = $args;
    }
    else
    {
      // predeprection of TestModule needs to merge old and new convetion
      $testModuleIdentifiers = array_unique(
          array_merge( glob( "*TestCase.php") , glob("*TestModule.php")  ) );
    }


    foreach ($testModuleIdentifiers as $moduleIdentifier )
    {
      self::runAllTestsOnTestModule( $moduleIdentifier , $filter );
    }

  }

}
?>