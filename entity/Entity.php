<?

abstract class Entity extends ArrayAccessible
{

  public function duplicate() {
    $arr = $this->toArray();
    unset($arr['id']);
    $entityModel = $this->getEntityModel();
    $id = $entityModel->insert($arr);

    return $entityModel->findById($id);
  }

  public static function __set_state($data) {
    $entityClassName = get_called_class();
    return new $entityClassName($data);
  }

  public function __construct($mixed = null) {
    if (is_array($mixed)) {

      $this->fromArray($mixed);
    }

  }

  public final function getFields($object = null) {
    if ($object === null)
      $object = $this;

    $reflect = new ReflectionClass($object);
    $props = $reflect->getProperties(ReflectionProperty::IS_PUBLIC);
    $fields = array();

    foreach ($props as $prop)
      $fields[] = $prop->getName();

    return $fields;
  }

  public function toArray() {
    $reflect = new ReflectionClass($this);
    $props   = $reflect->getProperties(ReflectionProperty::IS_PUBLIC);
    $values = array();

    foreach ($props as $prop) {
      $propname = $prop->getName();
      $values[  $propname ]   = $this->$propname;
    }

    return $values;
  }

  public function fromArray($data){
    $publicFields = array_keys($this->toArray());

    foreach ($data as $field => $value) {
      if (in_array( $field, $publicFields)) {
        $this->$field = $value;
      }
    }


  }

  public function isDirty($field = null) {
    if ($field != null)
      return $this->__original->$field != $this->$field;

    foreach ($this->getFields() as $fieldName)
      if ($this->isDirty( $fieldName))
        return true;

    return false;
  }

  public function __get($var) {
    // get original object from storage
    if ($var == '__original')
      return $this->getEntityModel()->findById($this->id);


    $getterName = "get{$var}";
    if (method_exists( $this, $getterName)) {
      return $this->$getterName();
    }
  }

  public function __set($var, $val) {
    $setterName = "set{$var}";
    if (method_exists( $this, $setterName)) {
      return $this->$setterName($val);
    }
    else {
      $this->$var = $val;
    }
  }

  public function __toString() {
    return var_export($this,true);
  }

  public function GetEntityModel() {
    $entityClassName = get_called_class();
    $entityModelClassName = "{$entityClassName}Model";
    $entityModel = $entityModelClassName::getInstance();

    return $entityModel;
  }

  public static function All($filter = array()) {
    return self::GetEntityModel()->find($filter);
  }


  public static function By($filter) {
    return self::GetEntityModel()->findFirst($filter);
  }

  public static function ById($id) {
    return self::GetEntityModel()->findById($id);
  }

  public static function Insert($entity) {
    $entityModel = self::getEntityModel();
    $id = $entityModel->insert($entity);

    return $entityModel->findById($id);
  }

  public function Update() {
    return self::getEntityModel()->update($this);
  }

  public function Delete() {
    return self::getEntityModel()->delete($this);
  }

  public static function Count($filter) {
    $entityModel = self::getEntityModel();

    return $entityModel->find($filter)->affected();
  }

  public static function Inject($data) {
    $entityModel = self::getEntityModel();

    return $entityModel->inject($data);
  }

  public static function Bulk($items) {
    return new EntityBulk($items);
  }

}

?>