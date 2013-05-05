<?
include_once(dirname(__FILE__)."/Base.php");

// base class for testing a model 
class TestModule {
	
	protected $base;
	
	public function __construct($base) {
		$this->base = $base;
		
		// select only methods from base class to test
		$baseclass = get_class($base);
		$rc = new ReflectionClass($baseclass);
		$methods = $rc->getMethods(ReflectionMethod::IS_PUBLIC);
		$methods_to_test = array();
		foreach ($methods as $rm) {
			$method = (array) $rm;
			if ($method["class"] == $baseclass) {
				$methods_to_test[] = $method["name"];
			}
		}
		
		$testing_methods = get_class_methods($this);
		$ok = true;
		foreach ($methods_to_test as $method_name) {
			if (substr($method_name,0,2)!='__') {
				$test_method_name = "test" . strtoupper($method_name[0]).substr($method_name,1);
				
				if (!in_array($test_method_name,$testing_methods)) {
					$missing_test_methods[] = $test_method_name;
					$ok = false;
				}
			
			}
		}
		if (!$ok) {
			echo "Missing test methods.<br /><pre>";
			foreach ($missing_test_methods as $test_method_name) {
				echo "public function {$test_method_name}() {\n\n}\n\n";
			}
			echo "</pre>";
			throw new Exception("Missing test methods for ".get_class($this)."!");
		}
	}
	
	private $assertCalled = false;
	
	// start the entire test
	public function start() {
		$ok = 0; $notok = 0; $tests = 0;
		
	
		
		$methods = get_class_methods($this);
		
		$class = get_class($this);
		foreach ($methods as $method) {	
			if (substr($method,0,4) == "test") {
				// echo "Running $method<br />";
				$this->assertCalled = false;
				call_user_func(array($this,$method));				
				if (!$this->assertCalled) {
					Console::WriteLine('Assert not called for method '.$class." => ".$method);
					$notok++;
				} else {
					Console::Write("Testing " .$class." => ".$method . " - SUCCESS " );
					$ok++;
				}
				$tests++;
				
			}			
		}
		Console::WriteLine("[ Test results for class {$class} : $ok OK | $notok UNTESTED | TOTAL : $tests ]");
	}
	
	protected function assertEquality($measured,$expected, $equal = true, $message = "") {
		$this->assertCalled = true;
		if ( ! ( ($measured == $expected) === $equal ) ) {
			echo "<pre>Asserting Equality items <strong style='text-decoration:underline;'>$message</strong> | ";
			print_r(array($measured,$expected));
			echo "</pre>";
			throw new Exception('Assert Equal failed for! '. $message);
		}
	}	
	
	protected function assertIdentity($measured,$expected, $equal = true, $message = "") {
		$this->assertCalled = true;
		if ( ! ( ($measured === $expected) === $equal ) ) {
			echo "<pre>Asserting Identity items <strong style='text-decoration:underline;'>$message</strong> | ";
			print_r(array($measured,$expected));
			echo "</pre>";
			throw new Exception('Assert identical failed! '. $message);
		}
	}

}
?>