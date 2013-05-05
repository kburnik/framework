<?

class NotImplementedException extends Exception {
	
	public function __construct($message = "Method not implemented!", $code = 0, Exception $previous = null) {
		$this->message = $message;
		$this->code = $code;
		$this->previous = $previous;
	}
}
?>