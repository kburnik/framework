<?php

abstract class ModelFactory
{

  public abstract function build();

  public static function Prepare()
  {
    $objectFactoryClassName = get_called_class();
    return new $objectFactoryClassName();
  }

  public function __call( $funcname, $args )
  {
    $prefix = substr($funcname,0,3);
    $varname = substr($funcname,3);
    $varname = lcfirst($varname);


    switch( $prefix )
    {

      case "get":
        return $this->$varname;
      break;

      case "set":
        $this->$varname = reset( $args );
        return $this;
      break;


      default:
        throw new Exception("Non-existing method '$funcname'");

    }

  }

  protected function __construct()
  {
  }
}

