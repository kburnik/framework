<?

class SurogateDataDriver implements IDataDriver {

  private static $realDataDriver;

  public static function SetRealDataDriver($realDataDriver) {
    self::$realDataDriver = $realDataDriver;
  }

  public static function GetRealDataDriver() {
    return self::$realDataDriver;
  }

  private static function CheckRealDataDriver() {
    if (!self::$realDataDriver instanceof IDataDriver) {
      throw new Exception("No Real Data Driver was set");
    }
  }

  private static function ForwardRequest($func, $args) {
    self::CheckRealDataDriver();

    $result = call_user_func_array(
        array(self::$realDataDriver, $func), $args);

    return $result;
  }

  public function update($entityType, $entityArray) {
  	return self::ForwardRequest(__FUNCTION__, func_get_args());
  }

  public function insert($entityType, $entityArray) {
  	return self::ForwardRequest(__FUNCTION__, func_get_args());
  }

  public function insertupdate($entityType, $entityArray) {
  	return self::ForwardRequest(__FUNCTION__, func_get_args());
  }

  public function delete($entityType, $entityArray) {
  	return self::ForwardRequest(__FUNCTION__, func_get_args());
  }

  public function deleteBy($sourceObjectName, $filterArray) {
    return self::ForwardRequest(__FUNCTION__, func_get_args());
  }

  public function count($entityType) {
  	return self::ForwardRequest(__FUNCTION__, func_get_args());
  }

  // chain
  public function find($entityType, $filter) {
  	return self::ForwardRequest(__FUNCTION__, func_get_args());
  }

  // chain
  public function select($entityType, $fields) {
  	return self::ForwardRequest(__FUNCTION__, func_get_args());
  }

  // chain
  public function orderBy($comparisonMixed) {
  	return self::ForwardRequest(__FUNCTION__, func_get_args());
  }

  // chain
  public function limit($start, $limit) {
  	return self::ForwardRequest(__FUNCTION__, func_get_args());
  }

  // Release the chain : return the result of the lasy operation
  public function ret() {
  	return self::ForwardRequest(__FUNCTION__, func_get_args());
  }

  // counts affected entries
  public function affected() {
  	return self::ForwardRequest(__FUNCTION__, func_get_args());
  }

  // return the entity field used for constructing the underlying data structure (e.g. mysql table)
  public function getEntityField() {
  	return self::ForwardRequest(__FUNCTION__, func_get_args());
  }

  public function execute($query) {
    return self::ForwardRequest(__FUNCTION__, func_get_args());
  }

  public function prepare($query, $types) {
    return self::ForwardRequest(__FUNCTION__, func_get_args());
  }

  public function executeWith() {
    return self::ForwardRequest(__FUNCTION__, func_get_args());
  }

  public function join($sourceObjectName,
  										 $refDataDriver,
  										 $refObjectName,
                       $resultingFieldName,
                       $joinBy,
                       $fields = null) {
    return self::ForwardRequest(__FUNCTION__, func_get_args());
  }

}

?>