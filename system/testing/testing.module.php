<? 
include_once("../system.php");

function runtest($array) {
	echo "<pre>";
	global $worktimes;
	$start = micronow();
	
	function testfunction($test_function,$key) {
		global $worktimes;
		echo "<h1>$key</h1> <br />";
		$start = micronow();
		$result = $test_function();
		$worktimes[] = $result["worktime"] = microdiff($start);
		print_r($result);
	}
	
	toeach($array,"testfunction",true);
	
	echo "Testing stats (time): ". json_encode(vector_stats($worktimes,"count,min,max,avg,sum")). "\n";	
	echo "</pre>"
	;
}


?>