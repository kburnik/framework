<?php

class EntityModelResponderRouter extends ApplicationRouter {
  protected $viewProviderFactory;

  public function __construct($routes, $viewProviderFactory) {
    $this->routes = $routes;

    if (!($viewProviderFactory instanceOf IViewProviderFactory)) {
      throw new Exception(
          "Expected instance of IViewProviderFactory, got: " .
          var_export( $viewProviderFactory , true ));
    }

    $this->viewProviderFactory = $viewProviderFactory;
  }

  private $params = array();

  // route to the controller
  public function route($url, $params) {
    $this->params = $params;
    $responder = $this->getControllerForRoute($url);
    if ($responder instanceOf IResponder) {
      return $responder;
    } else {
      throw new Exception('No route found');
    }
  }

  // redirect to another route when controller exits
  public function redirect($controller) {}

  // get the controller once route is found
  public function getController($controllerClassName, $controllerParams) {
    $entityClassName = $controllerParams['entity'];

    if ($entityClassName) {
      $subResponderClassName = "{$entityClassName}Responder";

      if ($this->viewProviderFactory->viewProviderExists(
            $subResponderClassName)) {
        $viewProviderFactoryKey = $subResponderClassName;
      } else {
        $viewProviderFactoryKey = $controllerClassName;
      }

      if (class_exists($subResponderClassName)) {
        $controllerClassName = $subResponderClassName;
      }

    } else {
      throw new Exception(
          "Entity class name invalid, got: " .
          var_export($entityClassName, true));
    }

    $viewProvider =
      $this->viewProviderFactory->getViewProvider($viewProviderFactoryKey);

    $mergedParams = array_merge(
        (array) $this->params ,
        (array) $controllerParams );

    $responder = new $controllerClassName($mergedParams, $viewProvider);

    return $responder;
  }
}
