<?

abstract class ApplicationRouter
{

  protected $routes;

  // default route to take when the controller does not provide one via exitEventParam
  protected $defaultExitRoute = null;


  // route to the controller
  public abstract function route( $url , $params );

  // redirect to another route when controller exits
  public abstract function redirect( $controller );

  // get the controller once route is found
  public abstract function getController( $controllerClassName , $controllerParams );


  public function __construct( $routes )
  {
    $this->routes = $routes;
  }


  protected function resolveParams( $params , $matchResults ) {

    foreach ( $params as $varName => $mapping ) {
      if (is_object($mapping))
        continue;

      preg_match_all('/\$\d+/',$mapping,$referenceMatches);

      $replacements = array();

      foreach ( $referenceMatches[0] as $refMatch )
      {
        $index = intval(substr( $refMatch,1 ) );
        $replacements[ $refMatch ] = $matchResults[ $index ];
      }

      $replacement = strtr( $mapping , $replacements );
      $params[ $varName ] = $replacement;

    }
    return $params;
  }

  protected function getControllerForRoute( $url )
  {

    $routes = $this->routes;


    $controllerMatched = false;

    // direct match
    if ( array_key_exists( $url , $routes ) )
    {

      $controllerMatched = true;

      list( $controllerClassName , $controllerParams , $defaultExitRoute ) = $routes[ $url ];

      $regexPattern = null;

    }
    else
    {
    // regex match

      foreach ( $routes as $pattern => $routeInstructions )
      {

        list( $className , $controllerParams , $defaultExitRoute ) = $routeInstructions;

        $regexPattern = "/{$pattern}/";

        if ( @preg_match( $regexPattern , $url , $matchResults ) )
        {

          $controllerClassName = $className;

          $controllerMatched = true;

          break;

        }

      }


    }



    if ( $controllerMatched )
    {


      $this->defaultExitRoute = $defaultExitRoute;


      // extend controller params with regex matches
      if ( $regexPattern !== null )
      {

        if ( is_array( $controllerParams ) )
          $controllerParams = $this->resolveParams( $controllerParams , $matchResults  );

      }


      $controller = $this->getController( $controllerClassName , $controllerParams );

      return $controller;

    }

    return null;


  }

}


?>