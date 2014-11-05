<?

interface IQueriedDataProvider extends IQueriedDataTransformer {
  public function cloneProvider();
  //// CONNECT/DISCONNECT
  public function connect($host=null,
                          $username=null,
                          $password=null,
                          $database=null);
  public function reconnect();
  public function disconnect();
  //// EXECUTE QUERIES
  public function execute($query);
  public function executeAll($queries);
  public function prepare($query, $types);
  // execute prepared statement with params.
  public function executeWith();
  //// COMMON QUERIES
  public function insert($table, $data);
  public function update($table, $data, $filter = null);
  public function insertUpdate($table, $data);
  public function delete($table, $filter = null);
  //// QUERY RESULTS
  public function getResult();
  public function getRowCount();
  public function getAffectedRowCount();
  public function getError();
  public function getErrorNumber();
  //// STRUCTURE AND DETAILS
  public function getObjects();
  public function getTables();
  public function getTableModificationTime($table);
  public function getViews();
  public function getTableDetails();
  public function getFields($table);
  public function getEnum($table, $field);
  public function getPrimaryKey($table);
  public function getStorageSize();
  public function prepareTableQuery($table, $structure);
  public function prepareTable($table, $structure);
  //// MAINTENANCE
  public function drop($tables);
  public function truncate($tables);
  public function repair($tables);
  public function optimize($tables);
  //// UTILITY :: Variable storage
  public function storeValue($variable, $value);
  public function loadValue($variable);
  public function clearValue($variable);
  public function existsValue($variable);
}

?>