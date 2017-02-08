<?php

class StorageViewProvider extends ViewProvider {
  private $templates = null;

  public function __construct($storage) {
    if (!($storage instanceof Storage)) {
      throw new Exception('StorageViewProvider :: Invalid Storage provided!');
    }

    $this->templates = $storage;
  }

  public function getTemplate($viewKey) {
    return $this->templates[$viewKey];
  }

  public function containsTemplate($viewKey) {
    return $this->templates->exists($viewKey) ;
  }
}
