<?

abstract class EntityField implements IEntityField {

  // todo
  public $fieldName;
  protected $isPrimaryKey = false;
  protected $isNullField = false;
  protected $nullStatusSet = false;
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
    // Reset all vars.
    $this->descriptor = array();
    $this->indices = array();
    $this->isPrimaryKey = false;
    $this->isNullField = false;
    $this->nullStatusSet = false;
  }

  public function ret() {
    // add null status
    if (!$this->nullStatusSet) {
      if ($this->isNullField)
        $this->IsNull();
      else
        $this->NotNull();
    }

    $res = implode(" ", $this->descriptor);
    $resIndex = implode(", ", $this->indices);
    $this->reset();

    return array($res, $resIndex);
  }

}

?>