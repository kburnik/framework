<?php

abstract class ViewProvider implements IViewProvider {
  function getView($viewKey, $data) {
    if ($this->containsTemplate( $viewKey ) ){
      return produce($this->getTemplate($viewKey),$data);
    } else {
      throw new Exception('ViewProvider :: Missing view "' . $viewKey.'"');
    }
  }

  function getResources($viewKey, $section) {
    return array();
  }
}
