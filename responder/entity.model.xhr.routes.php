<?php

return array(


  '\/(.*)\/([0-9]+)(\?(.*)){0,1}' =>
    array(
      'EntityModelXHRResponder' ,
      array( 'action' => 'findById' ) ,
      array( 'entity' => 1 , 'id' => 2 )
    ) ,

  '\/(.*)\/@([A-Za-z_]+[A-Za-z0-9_]*)(\?(.*)){0,1}' =>
    array(
      'EntityModelXHRResponder' ,
      array(),
      array( 'entity' => 1 , 'action' => 2 )
    ) ,

  '\/(.*)\/(\?(.*)){0,1}' =>
    array(
      'EntityModelXHRResponder' ,
      array( 'action' => 'find' ) ,
      array( 'entity' => 1 )
    ) ,
);

