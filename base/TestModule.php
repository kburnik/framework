<?
include_once(dirname(__FILE__)."/Base.php");

// base class for testing a model 
class TestModule {
	
	protected $base;
	
	public function __construct($base) {
		$this->base = $base;
		
		// select only methods from base class to test
		$baseclass = $base;
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
			echo "Missing test methods.\n";
			foreach ($missing_test_methods as $test_method_name) {
				echo "public function {$test_method_name}() {\n\n}\n\n";
			}			
			throw new Exception("Missing test methods for ".get_class($this)."!");
		}
	}
	
	private $assertCalled = false;
	
	// start the entire test
	public function start() {
		
		
		$candidateMethods = get_class_methods($this);		
		foreach ($candidateMethods as $method) {	
			if (substr($method,0,4) == "test") {
				$testMethods[] = $method;
			}			
		}
		
		$methodCount = count($testMethods);
		
		
		$methodIndex = 0;
		
		$ok = 0; $notok = 0; $tests = 0;
		
		$class = get_class($this);
		
		foreach ($testMethods as $method) {				
			$methodIndex++;
			// echo "Running $method<br />";
			$this->assertCalled = false;
			
			$this->output("Testing " .$class." => ".$method .": " , "yellow" );
			call_user_func(array($this,$method));				
			
			
			if (!$this->assertCalled) {
				$notok++;
				$this->output("NO ASSERT CALLED $notok / $methodCount: ".$class." => ".$method."\n" , "red");
				
			} else {				
				$ok++;
				$this->output("SUCCESS $ok / $methodCount\n" ,"green" );
			}
			$tests++;				
		}
		
		if ($notok == 0) {
			$summary_color = "green";
		} else {
			$summary_color = "red";
		}
		
		$this->output("[ Test results for class {$class} : $ok OK | $notok UNTESTED | TOTAL : $tests ]\n",$summary_color);
	}
	
	private function output( $message , $color = "yellow" ) {
		echo ShellColors::getInstance()->getColoredString($message,$color);
	}
	
	private function outputError( $message , $data ) {
		$out = "$message\nData: ".var_export($data,true);
		$this->output( $message , "red" );
	}
	
	protected function assertEquality($measured,$expected, $equal = true, $message = "") {
		$this->assertCalled = true;
		if ( ! ( ($measured == $expected) === $equal ) ) {
			$this->outputError("Assert equality failed",array($measured,$expected));
			# echo "<pre>Asserting Equality items <strong style='text-decoration:underline;'>$message</strong> | ";
			# print_r(array($measured,$expected));
			# echo "</pre>";
			throw new Exception('Assert Equal failed for ' . var_export(array($measured,$expected),true) . $message);
		}
	}	
	
	protected function assertIdentity($measured,$expected, $equal = true, $message = "") {
		$this->assertCalled = true;
		if ( ! ( ($measured === $expected) === $equal ) ) {
			$this->outputError("Assert identity failed",array($measured,$expected));
			# echo "<pre>Asserting Identity items <strong style='text-decoration:underline;'>$message</strong> | ";
			# print_r(array($measured,$expected));
			# echo "</pre>";
			throw new Exception('Assert Identity failed for ' . var_export(array($measured,$expected),true) . $message);
		}
	}

}
?>