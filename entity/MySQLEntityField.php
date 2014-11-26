<?

class MySQLEntityField extends EntityField {

  public function PrimaryKey() {
    $this->isPrimaryKey = true;
    // todo: handle auto_increment not to be hardcoded, but an option!
    return $this->attach("PRIMARY KEY AUTO_INCREMENT");
  }

  public function ForeignKey($refTable, $refField) {
    $refTable =  strtolower($refTable);
    $fieldName = $this->fieldName;

    $string = "INDEX `{$fieldName}_index` (`$fieldName`), "
        ." FOREIGN KEY (`$fieldName`) REFERENCES `$refTable`(`$refField`)"
        ." ON DELETE SET NULL "
        ." ON UPDATE NO ACTION "
        ;

    $this->attachIndex($string);
  }

  public function FullText() {
    $this->isFullText = true;
  }

  public function Integer($size, $notNull = true) {
    return $this->attach("INT($size)");
  }

  public function Unsigned() {
    return $this->attach("UNSIGNED");
  }

  public function IsNull() {

    $this->nullStatusSet = true;
    $this->isNullField = true;
    return $this->attach("NULL");
  }

  public function NotNull() {
    $this->nullStatusSet = true;
    $this->isNullField = false;
    return $this->attach("NOT NULL");
  }

  public function VarChar($size) {
    return $this->attach("VARCHAR($size)");
  }

  public function Text() {
    return $this->attach("TEXT");
  }

  public function DateTime() {
    return $this->attach("DATETIME");
  }

  public function Timestamp() {
    return $this->attach("TIMESTAMP");
  }

  public function Date() {
    return $this->attach("DATE");
  }

  public function Time() {
    return $this->attach("TIME");
  }

  public function Decimal($total, $decimal) {
    return $this->attach("DECIMAL($total, $decimal)");
  }

  public function Enum() {
    $values = implode(', ', func_get_args());
    return $this->attach("ENUM($values)");
  }

  public function AutoEnum($className) {
    $refl = new ReflectionClass($className);
    $consts = $refl->getConstants();
    foreach ($consts as $const=>$value)
      $values[] = "'$value'";

    $list = implode(", ", $values);

    return $this->attach("ENUM($list)");
  }

}

?>