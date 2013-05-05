<?

abstract class RegexRequestValidator extends BaseRequestValidator {
	
	private $fieldRegexArray;
	public function __construct( $request = NULL) {
		parent::__construct( $request );
		
		$this->fieldRegexArray = $this->getRegexArray();
		
	}
	
	abstract function getRegexArray();	
	
	function isFieldValid( $field , $value, $request ) {
		if (!array_key_exists( $field, $this->fieldRegexArray )) {
			throw new Exception("Missing Field RegEx Array entry for field '{$field}'");
		}
		
		$pattern = $this->fieldRegexArray[ $field ];		
		
		if ($pattern === null) {
			Console::WriteLine("Warning! Got null pattern for field \"{$field}\" in RegexRequestValidator");
			return true;
		}

		$valid = preg_match( $pattern, $value  ) ;
		
		if (!$valid) {
			$this->setFieldErrorMessage( $field , $field );
		}
		
		return $valid;
	}
}

?>