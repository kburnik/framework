<?php

class EntityModelFactory implements IEntityModelFactory
{

  public function createModelForEntity( $entityClassName , $dataDriver = null )
  {

    $entityModelClassName  = "{$entityClassName}Model";

    if ( !class_exists( $entityModelClassName ) )
    {

      throw new Exception("No EntityModel found '{$entityModelClassName}'");

    }

    return new $entityModelClassName( $dataDriver );

  }


}

