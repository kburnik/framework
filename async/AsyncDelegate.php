<?

class AsyncDelegate {
	private $modelClassName;
	private $description;
	public function __construct($modelClassName , $description = null) {
		$this->modelClassName = $modelClassName;
		$this->description = $description;
	}
	
	public function __call($functionName,$params) {
		return Async::CallModelFunction(array( $this->modelClassName , $functionName ) , $params , $this->description);
	}
}


?>