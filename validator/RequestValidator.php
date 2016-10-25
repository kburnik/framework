<?php

abstract class RequestValidator implements IRequestValidator {

  protected $request, $fieldErrorMessages;

  public function __construct( $request ) {
    $this->request = $request;
  }

  function getRequestData() {
    return $this->request;
  }

  // set error message for field
  function setFieldErrorMessage( $field , $message ) {
    $this->fieldErrorMessages[ $field ] = $message;
  }

  // test if the whole request is valid
  function isRequestValid( ) {
    Console::WriteLine("RequestValidator :: Validation start");
    $requestValid = true;

    // the validation process
    $requestData = $this->getRequestData();

    foreach ($this->request as $field => $value) {
      if ( $this->isFieldValid( $field, $value, $requestData ) ) {

      } else {

        $requestValid = false;
      }
    }

    Console::WriteLine("RequestValidator :: Validation end");
    return $requestValid;
  }

  // return all error messages
  function getFieldErrorMessages()  {
    return $this->fieldErrorMessages;
  }

}

