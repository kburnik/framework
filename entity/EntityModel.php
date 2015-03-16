<?

abstract class EntityModel extends BaseSingleton {

  private static $modelInstances = array();

  // Name of the main entity this EnityModel represents.
  protected $entityClassName = null;

  // Name of the underlying object in the data storage.
  protected $sourceObjectName = null;

  // The IDataDriver object which communicates to the data source
  // (i.e. Database/InMemory/FileSystem).
  protected $dataDriver = null;

  // Entity Model dependencyResolver.
  protected $em = null;

  // Array of the joined object.
  protected $joinObjectResolver = array();

  // Results from query and filtering.
  private $results = array();

  // static
  public static function getInstance() {
    $entityModelClassName = get_called_class();
    if (!isset(self::$modelInstances[$entityModelClassName])) {
      self::$modelInstances[$entityModelClassName] =
          new $entityModelClassName();
    }
    return self::$modelInstances[$entityModelClassName];
  }

  public function __construct($dataDriver = null,
                              $sourceObjectName = null,
                              $dependencyResolver = null) {
    parent::__construct();

    if ($dataDriver === null)
      $dataDriver = $this->getDataDriver();

    $this->dataDriver = $dataDriver;

    if ($sourceObjectName === null)
      $sourceObjectName = $this->getSourceObjectName();

    if ($dependencyResolver === null)
      $dependencyResolver = new EntityModelDependencyResolver();

    $this->em = $dependencyResolver;
    $this->sourceObjectName = $sourceObjectName;
    $this->entityClassName = $this->getEntityClassName();

    // TODO: remove try-catch block. Check project exists instead.
    try {
      Project::getCurrent()->bindProjectAutoEventHandlers($this);
    } catch (Exception $ex) {

    }
  }

  public function __call($method, $args) {
    if (substr($method, 0, 2) == '__') {
      $driverMethodName = substr($method, 2);

      if (!method_exists($this->dataDriver, $driverMethodName)) {
        $dataDriverClassName = get_class($this->dataDriver);
        throw new Exception(
            "Missing method for {$dataDriverClassName}::{$method}");
      }

      $result = call_user_func_array(
          array($this->dataDriver, $driverMethodName), $args);

      return $this->toObjectArray($result);
    } else {
      parent::__call($method, $args);
    }
  }

  public function getEntityFields() {
    $reflect = new ReflectionClass($this->entityClassName);
    $props = $reflect->getProperties(ReflectionProperty::IS_PUBLIC);
    $fields = array();

    foreach ($props as $prop)
      $fields[] = $prop->getName();

    return $fields;
  }

  protected function getDataDriver() {
    if (!isset($this->dataDriver)) {
      $entityModelClassName = get_class($this);
      $dataDriverClassName = "{$entityModelClassName}DataDriver";

      if (!class_exists($dataDriverClassName))
        throw new Exception("Missing Data Driver '{$dataDriverClassName}'");

      $this->dataDriver = new $dataDriverClassName();
    }

    return $this->dataDriver;
  }

  public static function InjectDataDriver($entityModel, $dataDriver) {
    $entityModel->dataDriver = $dataDriver;
  }


  public function getEntityClassName($omitNamespace = false) {
    static $entityClassName;

    if (!isset($entityClassName)) {
      $className = get_class($this);
      $entityClassName = preg_replace('/(.*)Model/', '$1', $className);
    }

    if ($omitNamespace) {
      $parts = explode("\\", $entityClassName);
      return array_pop($parts);
    }

    return $entityClassName;
  }

  protected function getSourceObjectName() {
    return strtolower($this->getEntityClassName(true));
  }

  protected final function getEntityPublicFields() {
    $reflect = new ReflectionClass($this->entityClassName);
    $props = $reflect->getProperties(ReflectionProperty::IS_PUBLIC);
    $fields = array();

    foreach ($props as $key => $prop) {
      $propname = $prop->getName();
      $fields[] = $propname;
    }

    return $fields;
  }

  protected function _checkFilter($filterArray) {
    static $operators = array(':between', ':gt', ':lt', ':gteq', ':lteq', ':eq',
        ':ne', ':in', ':nin', ':or');

    if (!is_array($filterArray)) {
      throw new Exception("Expected array for filter, got : "
      . var_export($filterArray, true));
    }

    $filterKeys = array_keys($filterArray);

    $fields = $this->getEntityPublicFields();

    if ($filterKeys != array_intersect($filterKeys,
        array_merge($fields, $operators))) {
      $diff = array_diff($filterKeys, $fields);
      throw new Exception("Invalid filter, some fields don't exist: "
          . var_export($diff, true));
    }
  }

  protected function resolveEntityAsArray($entityMixed) {
    if (!is_array($entityMixed)) {
      $entityArray = $entityMixed->toArray();
    } else {
      $fields = $this->getEntityPublicFields();
      $entityArray = array_pick($entityMixed, $fields);
    }

    return $entityArray;
  }

  protected function _insertSingleEntity($entityMixed) {
    if (is_array($entityMixed) ||
        $entityMixed instanceOf $this->entityClassName) {

      $entityArray = $this->resolveEntityAsArray($entityMixed);
      return $this->dataDriver->insert($this->sourceObjectName, $entityArray);
    } else {
      throw new Exception(
        "Cannot insert object to model . Expected '{$this->entityClassName}'"
        . " or array of such. Got " . var_export($entityMixed, true));
    }
  }

  public function count() {
    return $this->dataDriver->count($this->sourceObjectName);
  }

  // Create entity from array.
  public function create($entityArray = array(), $joinResolver = null) {
    $entityClassName = $this->entityClassName;
    $entityObject = new $this->entityClassName($entityArray);

    // Resolve the joins.
    if ($joinResolver !== null)
    {
      foreach ($joinResolver as $resultingFieldName => $resolvingModel)
      {
        assert($entityArray[$resultingFieldName]);
        $entityObject->$resultingFieldName =
            $resolvingModel->create($entityArray[$resultingFieldName]);
      }
    }

    return $entityObject;
  }

  // Can be one article as array or object, or an array of article
  // arrays/objects. Returns ID of newly inserted entity.
  public function insert($mixed) {
    if (is_array($mixed) && count($mixed) > 0) {
      $firstItem = reset($mixed);

      if (is_array($firstItem) ||
          $firstItem instanceOf $this->entityClassName) {
        foreach ($mixed as $item)
          $result = $this->_insertSingleEntity($item);

        return $result;
      }
    }

    return $this->_insertSingleEntity($mixed);
  }

  // Update a single entity. Returns number of updated rows.
  public function update($entityMixed) {
    $entityArray = $this->resolveEntityAsArray($entityMixed);

    return $this->dataDriver->update($this->sourceObjectName, $entityArray);
  }

  // Insert if not exist. Update if exists. Return number of affected rows.
  public function insertupdate($entityMixed) {
    $entityArray = $this->resolveEntityAsArray($entityMixed);

    return $this->dataDriver->insertupdate($this->sourceObjectName,
                                           $entityArray);
  }

  // General delete via filter.
  public function deleteBy($filterArray) {
    return $this->dataDriver->deleteBy($this->sourceObjectName, $filterArray);
  }

  // Delete via id.
  public final function deleteById($id) {
    return $this->deleteBy(array('id' => $id));
  }

  // Delete a single entity (given as object, but deleted by ID).
  public final function delete($entityMixed) {
    return $this->deleteById($entityMixed['id']);
  }

  public function findById($id) {
    $results = $this->find(array('id' => $id))->ret();
    if (count($results) == 0)
      return null;

    return reset($results);
  }

  public function findFirst($filterArray) {
    $results = $this->find($filterArray)->ret();

    if (count($results) == 0)
      return null;

    return reset($results);
  }

  // chains
  public function find($filterArray = array()) {
    $this->_checkFilter($filterArray);

    // chain start
    $this->dataDriver->find($this->sourceObjectName, $filterArray);

    return $this;
  }

  public function findFullText($query, $fields) {
    $this->dataDriver->findFullText($this->sourceObjectName, $query, $fields);
    return $this;
  }

  // chains
  public function orderBy($comparison) {
    if (!($this->dataDriver instanceOf IDataDriver))
      throw new Exception('Cannot sort, no selection made');

    $this->dataDriver->orderBy($comparison);

    return $this;
  }

  // chains
  public function limit($start, $limit) {
    if (!($this->dataDriver instanceOf IDataDriver))
      throw new Exception('Cannot limit, no selection made');

    $this->dataDriver->limit($start, $limit);

    return $this;
  }

  protected function toObjectArray($array) {
    if (!is_array($array))
      throw new Exception("Cannot convert to object array, got: " .
          var_export($array, true));

    foreach ($array as $i => $entityArray)
      $array[$i] = $this->create($entityArray, $this->joinObjectResolver);

    return $array;
  }

  // Releases the chain.
  public function ret() {
    $dataResults = $this->dataDriver->ret();
    $results = $this->toObjectArray($dataResults);
    $this->joinObjectResolver = array();

    return $results;
  }

  public function inject($data) {
    return $this->toObjectArray($data);
  }

  public function extract() {
    $fields = func_get_args();

    if (count($fields) == 1 && is_array($fields[0]))
      $fields = $fields[0];

    $allowedFields = $fields;

    // Add joining field names to allowed ones.
    foreach ($this->joinObjectResolver as $resultingFieldName => $ignore)
      if (in_array($resultingFieldName, $fields))
        $allowedFields[] = $resultingFieldName;
      else
        echo "$resultingFieldName not in \$fields\n";

    $results = $this
        ->dataDriver
        ->select($this->sourceObjectName, $allowedFields)
        ->ret();

    // Handle the joins.
    foreach ($results as $i => $entityArray) {
      foreach ($this->joinObjectResolver as
          $resultingFieldName => $resolvingModel) {

        if (in_array($resultingFieldName, $fields)) {
          assert($entityArray[$resultingFieldName] != null);
          $results[$i][$resultingFieldName] =
              $resolvingModel->create($entityArray[$resultingFieldName]);
        }
      }
    }

    $this->joinObjectResolver = array();

    return $results;
  }

  public function vectorOf() {
    $results = call_user_func_array(array($this, 'extract'), func_get_args());

    $vector = array();
    foreach ($results as $row)
      foreach ($row as $field => $value)
        $vector[] = $value;

    return $vector;
  }

  public function affected() {
    return $this->dataDriver->affected();
  }

  public function join($refModel,
                       $resultingFieldName,
                       $joinBy,
                       $fields = null) {
    if (count($fields) && is_array(reset($fields)))
      $fields = reset($fields);

    $refObjectName = $refModel->sourceObjectName;

    $this->dataDriver->join(
        $this->sourceObjectName,
        $refModel->getDataDriver(),
        $refObjectName,
        $resultingFieldName,
        $joinBy,
        $fields);

    if ($fields == null || count($fields) == 0)
      $this->joinObjectResolver[$resultingFieldName] = $refModel;

    return $this;
  }

  public function handleExtra($event, $entityMixed, $extraName, $extraData) {
    // Handles extra data such as bindings in other models
    // (e.g. images in a product).
  }

  // Returns entity bulk to do bulk operations on results.
  public function bulk() {
    return new EntityBulk($this->ret());
  }

  public function retUniqueBy($fields = array()) {
    $results = $this->ret();
    $groups = Array();
    foreach ($results as $result) {
      $vals = array_pick($result->toArray(), $fields);
      $key = implode(", ", $vals);

      if (!array_key_exists($key, $groups))
        $groups[$key] = $result;
    }

    return $groups;
  }

} // class

?>