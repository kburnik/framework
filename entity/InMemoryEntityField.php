<?

class InMemoryEntityField extends EntityField
{

  public function PrimaryKey()
  {
    $this->isPrimaryKey = true;
    return $this->attach("IN_MEMORY_PRIMARY_KEY()");
  }

  public function ForeignKey( $refTable , $refField )
  {
    return $this->attach("IN_MEMORY_FOREIGN_KEY($refTable,$refField)");;
  }


  public function Integer($size , $notNull = true)
  {

    return $this->attach("IN_MEMORY_INTEGER($size)");
  }


  public function Unsigned()
  {
    return $this->attach("IN_MEMORY_UNSIGNED");
  }

  public function IsNull()
  {
    $this->nullStatusSet = true;
    $this->isNullField = true;
    return $this->attach("IN_MEMORY_NULL");
  }

  public function NotNull()
  {
    $this->nullStatusSet = true;
    $this->isNullField = false;
    return $this->attach("IN_MEMORY_NOT_NULL");
  }

  public function VarChar( $size )
  {
    return $this->attach("IN_MEMORY_VARCHAR($size)");
  }

  public function Text( )
  {
    return $this->attach("IN_MEMORY_TEXT()");
  }

  public function DateTime()
  {
    return $this->attach("IN_MEMORY_DATETIME()");
  }

  public function TimeStamp()
  {
    return $this->attach("IN_MEMORY_TIMESTAMP()");
  }

  public function Date()
  {
    return $this->attach("IN_MEMORY_DATE()");
  }

  public function Time()
  {
    return $this->attach("IN_MEMORY_TIME()");
  }


  public function Decimal( $total , $decimal )
  {
    return $this->attach("IN_MEMORY_DECIMAL($total,$decimal)");
  }

  public function Enum()
  {
    $values = implode(',',func_get_args());
    return $this->attach("IN_MEMORY_ENUM($values)");
  }

  public function AutoEnum( $className )
  {
    $refl = new ReflectionClass($className);
    $consts = $refl->getConstants();
    foreach ( $consts as $const=>$value )
      $values[] = "'$value'";

    $list = implode(",",$values);

    return $this->attach("IN_MEMORY_ENUM($list)");

  }

}

?>