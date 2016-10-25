<?php


abstract class Storage extends Base implements IStorage {

  abstract function load();
  abstract function store();

  private $dataChanged = false;

  protected function hasDataChanged() {
    return $this->dataChanged;
  }

  protected $data = array();

  function getEventHandlerInterface() {
    return "IStorageEventHandler";
  }

  function read($variable,$default = null) {
    $this->onRead($variable);

    if ($this->exists($variable)) {
      return $this->data[$variable];
    } else {
      return $default;
    }

  }



  function write($variable,$value) {
    if ($variable === null) {
      $this->data[] = $value;
    } else {
      $this->data[$variable] = $value;
    }
    $this->dataChanged = true;
    $this->onWrite($variable,$value);
  }

  function clear($variable) {
    $this->dataChanged = true;
    $this->onClear($variable);
    unset($this->data[$variable]);
  }

  function exists($variable) {
    if ( is_array($variable) || is_object( $variable ) )
    {
      throw new Exception("Not offset type! " . var_export( $variable , true));
    }
    return isset($this->data[$variable]);
  }


  protected function getData() {
    return $this->data;
  }

  protected function setData($data) {
    if (!is_array($data)) $data = (array) $data;
    $this->data = $data;
  }

  function __construct() {
    $this->load();
  }

  function __destruct() {
    $this->store();
  }


  // ArrayOffset
  public function offsetSet($offset, $value) {
        $this->write($offset,$value);
    }
    public function offsetExists($offset) {
        return $this->exists($offset);
    }
    public function offsetUnset($offset) {
        $this->clear($offset);
    }
    public function offsetGet($offset) {
        return $this->read($offset);
    }

  // IteratorAggregate
  public function getIterator() {
    if (!is_array($this->data)) $this->data = array();
    return new ArrayIterator($this->data);
  }

  function valid() {
        // var_dump(__METHOD__);
        return isset($this->data[$this->position]);
    }

  // Countable
  function count() {
    return count($this->data);
  }

  // Serializable
  public function serialize() {
        return serialize($this->data);
    }

    public function unserialize($data) {
        $this->data = unserialize($data);
    }

}

