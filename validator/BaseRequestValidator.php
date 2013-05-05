<?

abstract class BaseRequestValidator extends RequestValidator {
	
	protected $viewProvider;
	
	
	
	public function __construct( $request = NULL) {
		if ($request == NULL ) $request = $_REQUEST;
		
		// filter only used fields		
		$request = array_intersect_key( $request ,  array_flip( $this->getUsedFields() ));
		
		parent::__construct( $request );
		
		$this->viewProvider = $this->getViewProvider();
		
		if ( !( $this->viewProvider instanceof ViewProvider ) ) {
			throw new Exception('No View provider for messages provided!');
		}
		
	}
	
	
	abstract function getUsedFields() ;
	public abstract function getViewProvider() ;
	
	// override
	function setFieldErrorMessage( $field , $viewKey ) {
		$message = $this->viewProvider->getView( $viewKey , $this->getRequestData() );
		parent::setFieldErrorMessage( $field, $message );
	}


	// return the success message
	function getSuccessMessage() {
		return $this->viewProvider->getView('request_success', $this->getRequestData() );
	}
	
	// return the error message
	function getErrorMessage() {
		return $this->viewProvider->getView('request_error', $this->getRequestData() );
	}
	
}

?>