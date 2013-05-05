<?

class AsyncDelegate {
	private $modelClassName;
	public function __construct($modelClassName) {
		$this->modelClassName = $modelClassName;
	}
	
	public function __call($functionName,$params) {
		return Async::CallModelFunction(array( $this->modelClassName , $functionName ) , $params);
	}
}


?>