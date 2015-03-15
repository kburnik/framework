<?

abstract class Controller extends Base implements ArrayAccess, IteratorAggregate, Countable
{

  protected $params;

  protected $viewProvider;


  public $exited = false;

  public $exitEventName;

  public $exitEventParam;


  // controller decides to exit ( router should then redirect )
  public function abort( $exitEventName , $exitEventParam )
  {
    $this->exited = true;
    $this->exitEventName = $exitEventName;
    $this->exitEventParam = $exitEventParam;
  }

  public function __construct($template = null , $params = array() , $viewProvider = null )
  {

    $this->params = $params;
    $this->viewProvider = $viewProvider;
    $models = $this->getDependencies();
    $this->inject( $models );
    $this->initialize();

    // produce all parts if template is set
    if ($template !== null && file_exists($template)) {
      echo produceview($template,$this);
    }

  }

  public function setParams( $params )
  {
    $this->params = $params;
  }


  // inject dependencies ( models )
  protected function inject()
  {

    $paramNames = func_get_args();



    if ( count( $paramNames ) > 0 && is_array( $paramNames[0] ) )
    {
      $dependencies = $paramNames[0];
    }

    foreach ( $dependencies as $varName => $classType )
    {
      $modelClassName = $this->params[ $varName ];

      if ( empty( $modelClassName ) )
      {
        throw new Exception( "Missing dependency in params: $varName"  );
      }
      if ( ! class_exists( $modelClassName ) )
      {
        throw new Exception( "Missing dependency class in params: $varName => $modelClassName"  );
      }

      /*
      if ( !is_subclass_of( $modelClassName , $classType , true ) )
      {
        throw new Exception( "Wrong dependency class type in params: $varName => $modelClassName , should be instance of $classType "  );
      }
      */

      $this->$varName = $modelClassName::getInstance();

    }

  }


  // return array of used models
  public abstract function getDependencies();

  // initialize object
  public abstract function initialize();


  public function setViewProvider( $viewProvider )
  {
    if ( ! ( $viewProvider instanceOf IViewProvider ) )
    {
      throw new Exception("Expected IViewProvider, got: " . var_export( $viewProvider , true ));

    }

    $this->viewProvider = $viewProvider;
  }

  public function getViewProvider()
  {

    if ( ! ( $this->viewProvider instanceOf IViewProvider ) )
    {
      throw new Exception("No valid ViewProvider was set, current  = " . var_export( $this->viewProvider , true ));

    }

    return $this->viewProvider;
  }


  // bind viewkey to view and produce data
  public function bind( $viewKey , $data )
  {

    return $this->getViewProvider()->getView( $viewKey , $data );

  }

  public static function getView($filename,$values = null) {
    return produce(get_once('./views/'.$filename),$values);
  }

  // ArrayOffset
  public function offsetGet($offset)
  {
        return
    (method_exists($this,$offset))
      ?
        $this->$offset()
      :
        $this->$offset
      ;
    }

    public function offsetExists($offset)
  {
        return method_exists($this,$offset);
    }

  public function offsetSet($offset, $value)
  {
        //
    }

    public function offsetUnset($offset)
  {
        //
    }


  // IteratorAggregate
  public function getIterator()
  {
    // if (!is_array($this->data)) $this->data = array();
    // return new ArrayIterator($this->data);
  }

  function valid()
  {
        // var_dump(__METHOD__);
        // return isset($this->data[$this->position]);
    }

  // Countable
  function count()
  {
    // return count($this->data);
  }


}

?>
