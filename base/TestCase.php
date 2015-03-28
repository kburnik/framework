<?

// Base class for testing a model.
abstract class TestCase {

  // For derived classes to specify from which classes to inherit the
  // testing methods.
  public static $inherits = array();

  protected $assertCalled = false;
  protected $assertCount = 0;
  private $summary;
  private $filter;
  private $reporter;
  private $coloredOutput = true;

  public static function runAllTestsOnTestModule($mixedModule,
                                                 $filter = null,
                                                 $summary = false,
                                                 $reporter = null) {
    $basename = basename($mixedModule);
    $class = str_replace('.php', '', $basename);

    if ( class_exists( $class ) ) {
      $testCase = new $class();
      $testCase->setSummary($summary);
      $testCase->setFilter($filter);
      $testCase->setReporter($reporter);
      return $testCase->start();
    } else {
      throw new Exception( "Class does not exist : $class" );
    }
  }

  // args can be empty, list of filenames or list of classes to test
  public static function run($args, $filter = null, $summary = false, $reporter = null) {
    if (!defined('SHELL_MODE')) {
      ob_end_flush();
      ob_flush();
      flush();

      define('SHELL_MODE',true);
    }

    if (count($args) > 0 ) {
      $testModuleIdentifiers = $args;
    } else {
      // predeprection of TestModule needs to merge old and new convetion
      $testModuleIdentifiers = array_unique(
          array_merge( glob( "*TestCase.php") , glob("*TestModule.php")  ) );
    }

    $failed = 0;

    foreach ($testModuleIdentifiers as $moduleIdentifier) {
      $failed += self::runAllTestsOnTestModule($moduleIdentifier,
                                               $filter,
                                               $summary,
                                               $reporter);
    }

    return $failed;
  }

  public function __construct() {}

  public function setSummary($summary) {
    $this->summary = $summary;
  }

  public function setFilter($filter) {
    $this->filter = $filter;
  }

  public function setReporter($reporter) {
    $this->reporter = $reporter;
  }

  public function setColoredOutput($coloredOutput) {
    $this->coloredOutput = $coloredOutput;
  }

  private function report($event, $args) {
    if ($this->reporter)
      $this->reporter->reportEvent($event, $args);
  }

  // start the entire test
  public function start() {
    if ($this->filter != null)
      $this->output("Using filter: $filter\n");

    $startTime = microtime(true);
    $derivedClassName = get_class($this);
    $testReflectionMethods = $this->getTestUnitMethods();

    foreach ($testReflectionMethods as $method) {
      if ($this->filter != null && !preg_match("/{$this->filter}/u", $method))
        continue;

      $testMethods[] = $method->name;
    }

    $methodCount = count($testMethods);
    $methodIndex = 0;
    $methodsPassed = 0;
    $methodsNotAsserted = 0;
    $methodsCalled = 0;
    $class = get_class($this);

    if (!is_array($testMethods)) {
      $this->outputWarning("No tests in: $class\n");
      return 0;
    }

    $this->report("testSuiteStarted", array("name" => "$class"));

    foreach ($testMethods as $method) {
      $methodIndex++;

      $this->output("{$class}::{$method}: ", "brown");
      $this->report("testStarted", array("name" => "$class.$method"));

      $testStartTime = microtime( true );
      $testCaseObject = new $derivedClassName();

      try  {
        call_user_func(array($testCaseObject, $method));
        $methodsPassed++;
        $currentMethodPassed = true;
      } catch (AssertException $ex) {
        $currentMethodPassed = false;
      }

      $testRunTime = round((microtime(true) - $testStartTime) * 1000, 2);

      if (!$testCaseObject->assertCalled) {
        $methodsNotAsserted++;

        $this->outputWarning(
            "NO ASSERT in: {$class}::{$method}");

      } else {

        $this->output($testCaseObject->assertCount . " asserts",
                      "light_gray");
        $this->output(" {$testRunTime} ms",
                      "cyan");

        if ($currentMethodPassed) {
          $this->output(" OK {$methodIndex}/{$methodCount}",
                        "green");
        } else {
          $this->output(" FAIL {$methodIndex}/{$methodCount}",
                        "red");

          $this->report("testFailed",array(
              "name" => "$class.$method",
              "message" => $ex->getTestMessage(),
              "details" => $ex->getMessage(),
              "expected" => var_export($ex->getExpected(), true),
              "actual" => var_export($ex->getMeasured(), true)));

        }

      }

      $this->output("\n");
      $this->report("testFinished", array("name" => "$class.$method",
          "duration" => $testRunTime));

      // destroy the object
      unset($testCaseObject);
      $methodsCalled++;
    }

    if ($methodsNotAsserted > 0) {
      $summary_color = "yellow";
    } else if ($methodsPassed == $methodsCalled) {
      $summary_color = "green";
    } else {
      $summary_color = "red";
    }

    $totalRunTime = round( (microtime( true ) - $startTime) * 1000 , 2 );

    $methodsFailed = $methodsCalled - $methodsPassed;

    $this->report("testSuiteFinished", array("name" => "$class"));

    $this->outputSummary(
          "[ {$class} : "
        . "{$methodsPassed} PASSED | "
        . "{$methodsFailed} FAILED | "
        . "{$methodsNotAsserted} UNTESTED | "
        . "{$methodsCalled} TOTAL | "
        . "Runtime: {$totalRunTime} ms ]\n"
      , $summary_color

    );

    return $methodsFailed;
  }

  private function isTestUnitMethod($reflectionMethod, $derivedClassName) {
    $rm = (array) $reflectionMethod;
    $methodOwnerOk = false;

    if (is_array( $derivedClassName::$inherits )) {
      $methodOwnerOk = in_array($rm['class'] , $derivedClassName::$inherits);
    }

    $methodOwnerOk = $methodOwnerOk || ( $rm['class'] == $derivedClassName );

    return ( $methodOwnerOk && substr($rm['name'],0,2) != '__' );
  }

  private function getTestUnitMethods() {
    $derivedClassName = get_class( $this );
    $rc = new ReflectionClass( $derivedClassName );
    $candidateMethods = $rc->getMethods(ReflectionMethod::IS_PUBLIC);

    foreach ( $candidateMethods as $i => $candidateMethod ) {
      if (!$this->isTestUnitMethod($candidateMethod , $derivedClassName)) {
        unset( $candidateMethods[ $i ] ) ;
      }
    }

    $candidateMethods = array_values( $candidateMethods );

    return $candidateMethods;
  }

  private function outputInternal($message,
                                  $color = "gray",
                                  $stream = "stdout",
                                  $force = false) {
    if ($this->summary && !$force)
      return;

    if ($this->coloredOutput) {
      file_put_contents("php://$stream",
          ShellColors::getInstance()->getColoredString($message, $color));
    } else {
      file_put_contents("php://$stream", $message);
    }

  }

  private function output($message, $color = "yellow") {
    $this->outputInternal($message, $color, "stdout");
  }

  private function outputWarning($message, $color = "yellow") {
    $this->outputInternal($message, $color, "stderr");
  }

  private function outputError($message, $color = "red") {
    $this->outputInternal($message, $color, "stderr");
  }

  private function outputSummary($message, $color) {
    $this->outputInternal($message, $color, "stdout", true);
  }

  private function showDiff($expected, $measured) {
    $temp_dir = sys_get_temp_dir();
    $temp_expected = tempnam($temp_dir, "expected_");
    $temp_measured = tempnam($temp_dir, "measured_");
    file_put_contents($temp_expected, var_export($expected, true)."\n");
    file_put_contents($temp_measured, var_export($measured, true)."\n");
    file_put_contents("php://stdout", "\n");
    file_put_contents("php://stdout",
        `git diff --color --no-index $temp_expected $temp_measured | tail -n+6`);
    unlink($temp_expected);
    unlink($temp_measured);
  }

  private function getAssertCallPosition() {
    $dbt = debug_backtrace();
    array_shift($dbt);

    $i = 0;
    while (empty($file) && ++$i < count($dbt)) {
      extract(array_pick($dbt[$i], array('file','line')));
    }
    $data = file($file);
    $file = str_replace(getcwd(), '.', $file);

    return array($file, $line, $data[$line-1]);
  }


  private function assertInternal($expected,
                                  $measured,
                                  $pass,
                                  $assert_type,
                                  $message = "") {
    $this->assertCalled = true;
    $this->assertCount++;

    if (!$pass) {
      list($file, $line, $contents) = $this->getAssertCallPosition();
      $this->outputError("\nAssert $assert_type failed." .
                         " Diff is displayed below.\n" .
                         "$file:$line: $contents\n");
      if ($message) {
        $this->outputError("Fail message: ");
        $this->outputWarning("$message\n");
      }

      $this->showDiff($expected, $measured);

      throw new AssertException(
        "Assert $assert_type failed for "
            . var_export($outputArray, true) . "\n"
            . $message,
        0, null, $expected, $measured, $message
      );
    }
  }

  protected function assertEqual($expected, $measured, $message = "") {
    $this->assertInternal($expected,
                          $measured,
                          ($expected == $measured),
                          "Equal",
                          $message);
  }

  protected function assertIdentical($expected, $measured, $message = "") {
    $this->assertInternal($expected,
                          $measured,
                          ($expected === $measured),
                          "Identical",
                          $message);
  }

  protected function assertTrue($assertion, $message = "") {
    $this->assertIdentical(true, $assertion, $message);
  }

  protected function assertFalse($assertion, $message = "") {
    $this->assertIdentical(false, $assertion, $message);
  }

  protected function assertNull($assertion, $message = "") {
    $this->assertIdentical(null, $assertion, $message);
  }

}

?>