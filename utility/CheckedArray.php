<?php

abstract class CheckedArray {

  public function __construct($array) {
    foreach ($this as $field => $none) {
      if (!array_key_exists($field, $array)) {
        throw new Exception("Checked array fail in " . get_class($this) .
                            " for field \"$field\".");
      }

      $this->$field = $array[$field];
    }
  }

  public function __get($field) {
    throw new Exception("Cannot access empty field $field in CheckedArray "  . get_class($this));
  }
}

