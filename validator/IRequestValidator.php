<?

interface IRequestValidator {
  // Retreive the request data array.
  function getRequestData() ;
  function isFieldValid($field, $value, $request);
  function setFieldErrorMessage($field, $message);
  // Test if the whole request is valid.
  function isRequestValid() ;
  function getFieldErrorMessages() ;
  function getSuccessMessage();
  function getErrorMessage();
}

?>