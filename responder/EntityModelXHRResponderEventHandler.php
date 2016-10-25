<?php

class EntityModelXHRResponderEventHandler implements IEntityModelXHRResponderEventHandler {

  function onInsert( $responder , $entityModel , $data, $result )
  {
    $entityName = $entityModel->getEntityClassName();

    $responder->__setMessageView(
      'successInsert',
      array(
        "entityName" => $entityName,
        "id" => $result->id,
      )
    );
  }

  function onUpdate( $responder , $entityModel , $data, $result )
  {

    $entityName = $entityModel->getEntityClassName();
    if ( $result > 0 )
    {
      $responder->__setMessageView(
        'successUpdateChanges',
        array(
          "entityName" => $entityName,
          "id" => $data['id'],
        )
      );
    }
    else
    {
      $responder->__setMessageView(
        'successUpdateNoChanges',
        array()
      );
    }

  }

  function onDelete( $responder , $entityModel , $data, $result )
  {

    $entityName = $entityModel->getEntityClassName();

    if ( $result > 0 )
    {
      $responder->__setMessageView(
        'successDeleteChanges',
        array(
          "entityName" => $entityName,
          "id" => $data['id'],
        )
      );
    }
    else
    {
      $responder->__setMessageView(
        'successDeleteNoChanges',
        array()
      );

    }

  }


  public function onCommit( $responder , $entityModel , $data, $result )
  {
    // todo
  }
}

