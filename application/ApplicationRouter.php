<?php

abstract class ApplicationRouter {

  protected $routes;

  // Default route to take when the controller does not provide one via
  // exitEventParam.
  protected $defaultExitRoute = null;

  // Route to the controller.
  public abstract function route($url, $params);

  // Redirect to another route when controller exits.
  public abstract function redirect($controller);

  // Redirect to another route via URL.
  public function handleUrlRedirect($url) {
    throw new Exception(
      "This method must be overriden in the subclass of ApplicationRouter.");
  }

  // get the controller once route is found
  public abstract function getController($controllerClassName,
                                         $controllerParams);

  public function __construct($routes) {
    $this->routes = $routes;
  }

  protected function resolveParams($params, $matchResults) {
    foreach ($params as $varName => $mapping) {
      if (is_object($mapping))
        continue;

      preg_match_all('/\$\d+/', $mapping, $referenceMatches);

      $replacements=array();

      foreach ($referenceMatches[0] as $refMatch) {
        $index = intval(substr($refMatch, 1));
        $replacements[$refMatch] = $matchResults[$index];
      }

      $replacement = strtr($mapping, $replacements);
      $params[$varName] = $replacement;

    }

    return $params;
  }

  protected function getControllerForRoute($url) {
    $routes = $this->routes;
    $controllerMatched = false;

    if (array_key_exists($url, $routes)) {
      // Direct match.
      $controllerMatched = true;

      list($controllerClassName, $controllerParams, $defaultExitRoute) =
        $routes[$url];

      $regexPattern = null;

    } else {
      // RegEx match.
      foreach ($routes as $pattern => $routeInstructions) {
        $regexPattern = "/{$pattern}/";

        $match = @preg_match($regexPattern, $url, $matchResults);

        if (!$match)
          continue;

        // A preg match -> replace redirect.
        if (is_string($routeInstructions)) {
          $newRoute = preg_replace($regexPattern, $routeInstructions, $url);
          $this->handleUrlRedirect($newRoute);

          return;
        }

        list($className, $controllerParams, $defaultExitRoute) =
            $routeInstructions;

        $controllerClassName=$className;
        $controllerMatched=true;

        break;
      }

    }

    if (!$controllerMatched)
      return null;

    $this->defaultExitRoute = $defaultExitRoute;

    // extend controller params with regex matches
    if ($regexPattern !== null && is_array($controllerParams))
        $controllerParams =
            $this->resolveParams($controllerParams, $matchResults);

    $controller = $this->getController($controllerClassName, $controllerParams);

    return $controller;
  }
}
