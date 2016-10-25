<?php

class IntegrityMessage {
  private $data;

  public function write($data) {
    $this->data .= $data . "\n" ;
  }

  public function read() {
    return $this->data;
  }
}

abstract class EntityIntegrityChecker {
  protected $model;

  public function __construct() {
    $derivedClassName = get_class($this);
    $modelClassName =
        str_replace('IntegrityChecker', '', $derivedClassName) . "Model";
    $this->model = $modelClassName::getInstance();

  }

  public function getIntegrityResults() {
    $derivedClassName = get_class( $this );
    $rc = new ReflectionClass( $derivedClassName );
    $methods = $rc->getMethods(ReflectionMethod::IS_PUBLIC);

    $results = array();

    foreach ($methods as $method) {
      if ($method->class != $derivedClassName)
        continue;

      $method_name = $method->name;

      $message = new IntegrityMessage();
      $result = $this->$method_name($message);

      $results[] = array(
        "class" => $method->class,
        "method" => $method->name,
        "result" => $result,
        "message" => $message->read()
      );
    }
    return $results;
  }
}

