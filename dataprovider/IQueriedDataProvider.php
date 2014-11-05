<?



interface IQueriedDataProvider extends IQueriedDataTransformer {

  public function cloneProvider() ;

  //// CONNECT AND DISCONNECT

  // connect to the bound data provider
  public function connect($host=null,$username=null,$password=null,$database=null);

  // reconnect to the bound data provider
  public function reconnect();

  // disconnect from the bound data provider
  public function disconnect();


  //// EXECUTE QUERIES

  // execute query
  public function execute($query);

  // execute more queries
  public function executeAll($queries);


  // prepare a query
  public function prepare( $query , $types );

  // execute prepared statement with params
  public function executeWith();


  //// COMMON QUERIES

  // insert data to a table
  public function insert($table,$data);

  // update a table by filter
  public function update($table,$data,$filter = null);

  // insert or on duplicate key update
  public function insertUpdate($table,$data);

  // delete from table determined by filter
  public function delete($table,$filter = null);


  //// QUERY RESULTS

  // get the raw query result
  public function getResult();

  // get number of rows in resultset
  public function getRowCount();

  // get affected row count
  public function getAffectedRowCount();

  // get error Message
  public function getError();

  // get error number
  public function getErrorNumber();


  //// STRUCTURE AND DETAILS

  public function getObjects();

  public function getTables();

  public function getTableModificationTime( $table );

  public function getViews();

  public function getTableDetails();

  public function getFields($table);

  public function getEnum( $table , $field );

  public function getPrimaryKey($table);

  public function getStorageSize();

  public function prepareTableQuery($table,$structure);

  public function prepareTable($table,$structure);


  //// MAINTENANCE

  public function drop($tables);

  public function truncate($tables);

  public function repair($tables);

  public function optimize($tables);

  //// UTILITY :: Variable storage

  public function storeValue($variable,$value);

  public function loadValue($variable);

  public function clearValue($variable);

  public function existsValue($variable);


}


?>