<?php

abstract class OneTimeSettings
{

  protected function defaultInit()
  {

  }

  public function __construct( $data = array() )
  {

    $this->defaultInit();

    foreach ( $data as $varname => $value )
    {
      $protectedVarName = "_" . $varname;
      $this->$protectedVarName = $value;
    }

  }

  public function __get( $varname )
  {
    $protectedVarName = "_" . $varname;

    if ( ! isset( $this->$protectedVarName ) )
      throw new Exception("Settings variable '$varname' is not set");

    return $this->$protectedVarName;
  }

  public function __set( $varname, $value )
  {
    throw new Exception( "Cannot change one time settings" );
  }
}

