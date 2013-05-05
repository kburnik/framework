<?
// interface for a validation object, which can in turn return error and success messages
interface IRequestValidator {
	
	// retreive the request data array
	function getRequestData() ;
	
	// test if a field is valid
	function isFieldValid( $field , $value, $request );
		
	// set error message for field 
	function setFieldErrorMessage( $field , $message );
	
	// test if the whole request is valid
	function isRequestValid( ) ;
	
	// return all field error messages
	function getFieldErrorMessages() ;
	
	// return the success message
	function getSuccessMessage();
	
	// return the error message
	function getErrorMessage();
	
}

?>