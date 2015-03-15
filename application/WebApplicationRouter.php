<?

abstract class WebApplicationRouter extends ApplicationRouter {

  public abstract function getViewProvider( $controllerClassName );

  protected function produce($template, $data) {
    return produce($template, $data);
  }

  protected function produceView($viewFilename, $controller) {
    return produceview($viewFilename, $controller);
  }

  public static function exportvars( $vars ){
    return var_export( $vars, true );
  }

  public function route($url, $params) {

    list($templateViewFilename,
         $notFoundViewFilename,
         $errorViewFilename) = $params;

    try {
      $controller = $this->getControllerForRoute( $url );

      if ( $controller instanceOf Controller ) {
        if ($controller->exited) {
          $this->redirect($controller);
        } else {
          header('HTTP/1.1 200 Ok');
          return $this->produceView($templateViewFilename, $controller);
        }
      } else {
        header('HTTP/1.1 404 Not Found');
        return $this->produceView($notFoundViewFilename,
                                  array( "url" => $url ) );
      }
    } catch(Exception $ex) {
      header('HTTP/1.1 500 Internal Server Error');

      $out .= ("Exception\r\n\r\n");
      $out .= ($ex->getMessage() . " (Exception code: {$ex->getCode()})\r\n");
      $out .= ("\r\n");
      $out .= ("Thrown at". $ex->getFile() . '(' . $ex->getLine() ."):\r\n\r\n");

      if (file_exists($errorViewFilename)) {
        return $this->produceView($errorViewFilename, $out );
      }

      header('Content-type:text/plain');
      die($out);
    }
  }

  public function getController($controllerClassName, $controllerParams) {
    $viewProvider = $this->getViewProvider($controllerClassName);
    $controller = new $controllerClassName(null,
                                           $controllerParams,
                                           $viewProvider);

    return $controller;
  }

  public function redirect($controller) {
    $exitEventName = $controller->exitEventName;

    if ( $controller->exitEventParam != null ) {
      $url = $controller->exitEventParam;
    } else if ( $this->defaultExitRoute != null ) {
      $url = $this->defaultExitRoute;
    } else {
      throw new Exception(
          "No exitEventParam or default exite route specified for " .
          get_class($controller) );
    }

    header('location:' . $url);
    die();
  }
}

?>