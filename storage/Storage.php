<?php

abstract class Storage extends Base implements IStorage {
  private $dataChanged = false;
  protected $data = array();

  public function __construct() {
    $this->load();
  }

  public function __destruct() {
    $this->store();
  }

  public abstract function load();
  public abstract function store();

  protected function hasDataChanged() {
    return $this->dataChanged;
  }

  public function getEventHandlerInterface() {
    return "IStorageEventHandler";
  }

  public function read($variable,$default = null) {
    $this->onRead($variable);

    if ($this->exists($variable)) {
      return $this->data[$variable];
    } else {
      return $default;
    }
  }

  public function write($variable,$value) {
    if ($variable === null) {
      $this->data[] = $value;
    } else {
      $this->data[$variable] = $value;
    }
    $this->dataChanged = true;
    $this->onWrite($variable,$value);
  }

  public function clear($variable) {
    $this->dataChanged = true;
    $this->onClear($variable);
    unset($this->data[$variable]);
  }

  public function exists($variable) {
    if (is_array($variable) || is_object($variable)) {
      throw new Exception("Not offset type! " . var_export($variable, true));
    }

    return isset($this->data[$variable]);
  }

  protected function getData() {
    return $this->data;
  }

  protected function setData($data) {
    if (!is_array($data))
      $data = (array) $data;
    $this->data = $data;
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
    if (!is_array($this->data))
      $this->data = array();

    return new ArrayIterator($this->data);
  }

  public function valid() {
    return isset($this->data[$this->position]);
  }

  // Countable
  public function count() {
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
