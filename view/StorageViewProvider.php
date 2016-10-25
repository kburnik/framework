<?php


class StorageViewProvider extends ViewProvider {

  private $templates = null;
  function __construct( $storage ) {
    if (!($storage instanceof Storage)) {
      throw new Exception('StorageViewProvider :: Invalid Storage provided! ');
    }
    $this->templates = $storage;
  }

  function getTemplate( $viewKey ) {
    return $this->templates[ $viewKey ];
  }

  function containsTemplate( $viewKey ) {
    return $this->templates->exists( $viewKey ) ;
  }

}

