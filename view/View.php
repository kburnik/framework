<?
include_once(dirname(__FILE__)."/../base/Base.php");

abstract class View extends Base implements ArrayAccess, IteratorAggregate, Countable, Serializable {

  protected $params;

  protected $viewProvider;

  public function __construct($template = null , $params = array() , $viewProvider = null )
  {

    $this->params = $params;

    $this->viewProvider = $viewProvider;

    //

    $this->initialize();

    //


    $models = $this->getUsedModels();

    // produce all parts if template is set
    if ($template !== null && file_exists($template)) {
      echo produceview($template,$this);
    }

  }

  public function setParams( $params )
  {
    $this->params = $params;
  }


  // return array of used models
  public abstract function getUsedModels();

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
  public function offsetGet($offset) {
        return
    (method_exists($this,$offset))
      ?
        $this->$offset()
      :
        $this->$offset
      ;
    }

    public function offsetExists($offset) {
        return method_exists($this,$offset);
    }

  public function offsetSet($offset, $value) {
        //
    }

    public function offsetUnset($offset) {
        //
    }


  // IteratorAggregate
  public function getIterator() {
    // if (!is_array($this->data)) $this->data = array();
    // return new ArrayIterator($this->data);
  }

  function valid() {
        // var_dump(__METHOD__);
        // return isset($this->data[$this->position]);
    }

  // Countable
  function count() {
    // return count($this->data);
  }

  // Serializable
  public function serialize() {
        // return serialize($this->data);
    }

    public function unserialize($data) {
        // $this->data = unserialize($data);
    }

}

?>