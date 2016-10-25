<?php

abstract class EntityField implements IEntityField {

  // todo
  public $fieldName;
  protected $isPrimaryKey = false;
  protected $isNullField = false;
  protected $nullStatusSet = false;
  protected $isFullText = false;
  private $descriptor = array();
  private $indices = array();


  public function isPrimaryKey() {
    return $this->isPrimaryKey;
  }

  protected function attach($string) {
    $this->descriptor[] = $string;
    return $this;
  }

  protected function attachIndex($index) {
    $this->indices[] = $index;
    return $this;
  }

  public function reset() {
    $this->descriptor = array();
    $this->indices = array();
    $this->isFullText = false;
    $this->isPrimaryKey = false;
    $this->isNullField = false;
    $this->nullStatusSet = false;
  }

  public function ret() {
    if (!$this->nullStatusSet) {
      if ($this->isNullField)
        $this->IsNull();
      else
        $this->NotNull();
    }

    $res = implode(" ", $this->descriptor);
    $resIndex = implode(", ", $this->indices);
    $fullText = $this->isFullText;

    $this->reset();

    return array($res, $resIndex, $fullText);
  }

}

