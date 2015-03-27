<?php

class MySQLProvider extends Base implements IQueriedDataProvider {
  private $link = null;
  private $cache = array();
  private $query = "";
  private $result = null;
  private $worktimes = array();
  private $last_query = "";
  private $last_query_worktime = 0;
  private $parent_object = null;
  private $count = 0; // count of queries executed
  private $current_database;
  private $last_inserted_id, $last_query_affected_row_count;

  // Construction connection strings.
  private $host, $username, $password;
  private $database;

  private $storage_prepared = false;

  public function cloneProvider() {
    return new MySQLProvider(
        $this->host, $this->username, $this->password, $this->database);
  }

  // BASIC
  function __construct(
      $host, $username = null, $password = null, $database = null) {
    parent::__construct($this);
    $this->host = $host;
    $this->username = $username;
    $this->password=$password;
    $this->database = $database;

    return $this;
  }

  function reconnect() {
    if ($this->host == null) {
      throw new Exception("No host provided for connection!");
    } else {
      $this->disconnect();

      return $this->connect(
          $this->host, $this->username, $this->password, $this->database);
    }
  }

  protected function getEventHandlerInterface() {
    return IQueriedDataProviderEventHandler;
  }

  function __destruct() {
    $this->disconnect();
  }

  function connect(
      $host = null, $username = null, $password = null, $database = null) {

    if ($this->link != null) return;

    if ($host == null && $this->host != null) {
      $host = $this->host;
      $username = $this->username;
      $password = $this->password;
      $database = $this->database;
    } else {
      $this->host = $host;
      $this->username = $username;
      $this->password = $password;
      $this->database = $database;
    }

    $this->link = mysql_connect($host, $username, $password, true);

    $this->current_database = $database;
    mysql_select_db($database, $this->link);
    $this->onConnect($host, $username, $password, $database);

    return $this->result;
  }

  function disconnect() {
    $this->onDisconnect();
    @mysql_close($this->link);
    $this->link = null;
  }

  function getDatabase() {
    return $this->execute("select database();")->toCell();
  }

  function execute($query) {
    $this->connect();

    $this->last_query = $query;
    $this->onExecuteStart($query);
    $this->result = $result = mysql_query($query, $this->link);

    $this->last_query_affected_row_count = mysql_affected_rows($this->link);
    $this->last_inserted_id = mysql_insert_id($this->link);

    if ($result === false) {
      $errno = $this->getErrorNumber();
      $error = $this->getError();
    }

    if ($error != '') {
      $this->onError($query, $error, $errno);
    } else {
      $this->onExecuteComplete($query, $this->result);
    }

    return $this;
  }

  function executeAll($queries, $delimiter=";") {
    if (!is_array($queries))
      $queries = explode($delimiter, $queries);

    foreach ($queries as $query) {
      if (trim($query)!='') {
        $this->execute($query.";");
        if ($this->getError()!='') return false;
      }
    }

    return true;
  }

  public function useDatabase($db) {
    if ($this->link == null) {
      $this->connect();
    }
    if(!mysql_select_db($db, $this->link)) {
      $e = mysql_error($this->link);
      trigger_error("Database $db :" .
          mysql_errno($this->link) . ": ".$e, E_USER_ERROR);
    }
    $this->current_database = $db;
    return $this;
  }

  public function isDatabase($db) {
    return mysql_num_rows(
        $this->execute("show databases like '{$db}'")->result) > 0;
  }

  // DATA TRANSFORMING
  public function toCell() {
    if ($this->result instanceof mysqli_stmt) {

      return reset($this->toRow());
    } else if ($this->result && mysql_num_rows($this->result) > 0) {
      $ff = mysql_fetch_field($this->result, 0);
      $result = mysql_result($this->result, 0, $ff->name);
    } else {
      $result = null;
    }

    return $result;
  }

  public function toRow() {
    $data = $this->toArray();
    if (!is_assoc($data) && is_array($data) && count($data)>0) {

      return reset($data);
    } else {

      return array();
    }
  }

  public function toVector() {
    $data=$this->toArray();
    if (!count($data)) return array();
    $out=array();
    if (is_array($data)) {
      foreach($data as $item) $out[]=reset($item);
    }

    return $out;
  }

  public function toArray($assoc=MYSQL_ASSOC) {
    $resource = $this->result;

    if ($resource instanceof mysqli_stmt) {
      $out = array();
      while ($resource->fetch()) {
        $row = array();

        foreach ($this->preparedStatementResultParams as $field=>$val) {
          $row[$field] = $val;
        }

        $out[] =  $row;
      }

      // close the statement
      $resource->close();
    } else {
      if (!$resource)
        return null;

      $out = $row = array();

      while ($row=@mysql_fetch_array($resource, $assoc)) {
        $out[]=$row;
      }

      return $out;
    }

    return $out;
  }

  public function toArrayMap() {
    $data = $this->toArray();
    if (count($data)==0)
        return array();

    foreach($data as $item) {
      $out[reset($item)] = $item;
    }

    return $out;
  }

  public function toPairMap() {
    $data = $this->toArray();
    if (count($data)==0)
      return array();

    foreach($data as $item)
      $out[reset($item)] = array_pop($item);

    return $out;
  }

  public function toArrayGroup($field=null, $remove_grouped_field = false) {
    $data = $this->toArray();
    if (count($data) == 0)
      return array();

    // Take first field if none is defined.
    if ($field === null)
      $field = reset(array_keys(reset($data)));
    foreach($data as $item) {
      $field_value = $item[$field];
      if ($remove_grouped_field) unset($item[$field]);
      $out[$field_value][] = $item;
    }

    return $out;
  }

  // QUERY BUILDING
  public function generate_fields($data) {
    if ($this->link == null)
      $this->connect();

    $fields = $values = array();

    foreach ($data as $key => $value) {
      if (is_array($value) || is_object($value)) {
        throw new Exception(
            "Value not a valid for mysql!!\n"
            . var_export(array($key => $value), true));
      }

      $fields[]="`".$key."`";
      if ($value === null && substr($key, 0, 3) == 'id_') {
        $values[] = "null";
      } else {
        $values[]="\"".mysql_real_escape_string($value, $this->link)."\"";
      }
    }

    return array($fields, $values);
  }

  // Filters fields by given list or by table (if string provided).
  private function filter_fields($mixed = array(), $data) {
    $fields = (is_array($mixed)) ? $mixed  : $this->fields($mixed);

    if (is_assoc($data)) {
      $data = array_pick($data, $fields);
    } else {
      foreach ($data as $index=>$row)
        $data[$index] = array_pick($row, $fields);
    }

    return $data;
  }

  public function generate_values($row) {
    return
        "(\""
          . implode("\", \"", array_map("mysql_real_escape_string", $row))
        . "\")";
  }

  // COMMON QUERIES.
  // Inserts a single row or multiple rows into table.
  public function insert($table, $data) {
    if (!is_array($data)) {
      throw new Exception(
          'INSERT :: Expected array, got ' . var_export($data, true));
    }

    if (is_assoc($data)) {
      list($fields, $values) = $this->generate_fields($data);
      $fields="(".implode(", ", $fields).")";
      $values="(".implode(", ", $values).")";
    } else {
      list($fields, $values) = $this->generate_fields(reset($data));
      $fields="(".implode(", ", $fields).")";
      $values = implode(", ",
          array_map(array($this, "generate_values"), $data));
    }

    $q = "insert into `$table` {$fields} values {$values};";
    $this->execute($q);
    $out = $this->last_inserted_id;

    $this->onInsert($table, $data);

    return $out;
  }

  public function update($table, $data, $filter = null) {

    list($fields, $values) = $this->generate_fields($data);
    foreach ($fields as $key => $field)
      $updates[] = "{$field} = {$values[$key]}";

    $updates = implode("\n, ", $updates);
    $q = "update \n`$table`\n set \n{$updates} "
        . (($filter !== null) ?  $filter->toString() : "");
    $this->execute($q);
    $this->onUpdate($table, $data, $filter, $this->result,
        $this->getAffectedRowCount());

    return $this->getAffectedRowCount();
  }

  public function insertUpdate($table, $data) {
    // the insert part of the query

    list($fields, $values)=$this->generate_fields($data);
    $insert_fields = "(" . implode(", ", $fields) . ")";
    $insert_values = "(" . implode(", ", $values) . ")";

    // the update part of the query
    $updates = array();

    foreach ($fields as $key => $field)
      $updates[]="{$field}={$values[$key]}";

    $updates=implode(", ", $updates);

    $q = "insert into `$table` {$insert_fields} values {$insert_values} "
        . "ON DUPLICATE KEY UPDATE {$updates};";
    $this->execute($q);

    $id = $this->last_inserted_id;
    $out = $this->getAffectedRowCount();
    $this->onInsertUpdate($table, $data, $out);

    return $out;
  }

  public function delete($table, $filter = null) {
    $this->execute("delete from `$table` "
        . (($filter !== null) ?  $filter->toString() : ""));
    $out = $this->getAffectedRowCount();
    $this->onDelete($table, $filter, $out);

    return $out;
  }

  public function prepareTableQuery($table,
                                    $structure,
                                    $fullTexts = array(),
                                    $engine = "MyISAM") {

    $fields = "";

    if (!in_array("id", array_keys($structure))) {
      $optional_auto_primary_key =
          "id bigint (8) unsigned not null primary key auto_increment";
      $comma = ", ";
    } else {
      $comma = "";
    }

    foreach ($structure as $field => $struct) {

      if (!is_numeric($field)) {
        $_field_part = "`{$field}`";
      } else {
        $_field_part = "";
        $engine = "INNODB";
      }

      $fields .= "{$comma} {$_field_part} {$struct}\n\t\t\t";
      $comma = ", ";
    }

    if (count($fullTexts) > 0) {
      $full_text_indices =
          ",fulltext __ft_indices(`" . implode('`, `', $fullTexts) . "`)";
    }

    $query = "
    create table if not exists `{$table}` (
      {$optional_auto_primary_key}
      {$fields}
      {$full_text_indices}
    ) engine = $engine;
    ";

    return $query;
  }

  public function prepareTable($table,
                               $structure,
                               $full_text = array(),
                               $engine = "MyISAM") {
    $query = $this->prepareTableQuery($table, $structure, $full_text, $engine);

    if ($this->link == null)
      $this->connect();

    mysql_query($query, $this->link);
  }

  // QUERY RESULT

  public function getResult() {
    return $this->result;
  }

  public function getRowCount() {
    return mysql_num_rows($this->result);
  }

  public function getAffectedRowCount() {
    return $this->last_query_affected_row_count;
  }

  public function getError() {
    if ($this->link == null) {
      throw new Exception("No link present");
    }

    return mysql_error($this->link);
  }

  public function getErrorNumber() {
    return mysql_errno($this->link);
  }

  // DB STRUCTURE AND DETAILS
  public function getObjects() {
    $data = $this->execute("show tables;")->toArray();

    return reset(rotate_table($data));
  }

  public function getTables() {
    $q = "SHOW FULL TABLES from `{$this->current_database}`"
        . " where table_type like 'BASE TABLE'";
    $data = $this->execute($q)->toArray();
    if (!is_array($data) || count($data) == 0) {

      return array();
    } else {

      return reset(rotate_table($data));
    }
  }

  public function getTableModificationTime($table) {
    $q = "SELECT UPDATE_TIME
        FROM   information_schema.tables
        WHERE  TABLE_SCHEMA = '".$this->database."'
           AND TABLE_NAME = '".$table."'
    ";

    return $this->execute($q)->toCell();
  }

  public function getViews() {
    $q = "SHOW FULL TABLES where table_type like 'VIEW'";
    $data = $this->execute($q)->toArray();

    return reset(rotate_table($data));
  }

  public function getTableDetails() {
    $this->execute("SHOW TABLE STATUS;", true);

    while($row = mysql_fetch_array($this->result, MYSQL_ASSOC)) {
      $tables[$row['Name']]=$row;
    }

    return $tables;
  }

  public function getFields($table) {
    $fields = rotate_table($this->getFieldDetails($table));
    $this->cache[$this->current_database]["table_fields"][$table] =
        $fields['Field'];

    return $this->cache[$this->current_database]["table_fields"][$table];
  }

  public function getFieldDetails($table) {
    $this->cache[$this->current_database]["field_details"][$table] =
        $this->execute(
            "SHOW FULL FIELDS FROM `{$this->current_database}`.`$table`;")
                ->toArray();

    return $this->cache[$this->current_database]["field_details"][$table];
  }

  public function getEnum($table, $field) {

    if (in_array($table, $this->getTables())) {
      $query = "SHOW COLUMNS FROM `$table` LIKE '$field' ";
      $this->execute($query, true);
      $row = mysql_fetch_array($this->result, MYSQL_NUM);
      $regex = "/'(.*?)'/";
      if (preg_match_all($regex, $row[1], $enum_array) > 0) {
        $output = $enum_array[1];
      }
    }

    return $output;
  }

  public function getPrimaryKey($table) {
    if (!isset($this->cache[$this->current_database]["primary_key"][$table])) {
      $field_details = $this->getFieldDetails($table);
      foreach ($field_details as $row) {
        if ($row['Key'] == 'PRI') {
          $this->cache[$this->current_database]["primary_key"][$table] =
              $row['Field'];

          break;
        }
      }
    }

    return $this->cache[$this->current_database]["primary_key"][$table];
  }

  public function getStorageSize() {
    if (!isset($this->cache[$this->current_database]["db_size"])) {
      $t = $this->table_details();
      $size = 0;

      foreach ($t as $table)
        $size += $table['Data_length'] + $table['Index_length'];

      $this->cache[$this->current_database]["db_size"] = $size;
    }

    return $this->cache[$this->current_database]["db_size"];
  }

  // TABLE MAINTENANCE

  public function drop($tables) {
    $table_list = $tables;
    if (is_array($tables))
      $table_list = "`".implode("`, `", $tables)."`";
    else
      $table_list = "`$table_list`";

    $this->execute("DROP TABLE IF EXISTS {$table_list};");
    $this->onDrop($tables);
    return $this->result;
  }

  public function truncate($tables) {
    $tbls = $tables;
    if (!is_array($tbls))
      $tbls = explode(", ", $tbls);

    foreach ($tbls as $table) {
      $this->execute("TRUNCATE TABLE `{$table}`;");
      $r[] = $this->result;
    }

    $this->onTruncate($tables);

    return $r;
  }

  public function repair($tables) {
    $tbls = $tables;
    if (is_array($tbls))
      $tbls = implode("`, `", $tbls);

    $out = $this->execute("REPAIR TABLE `{$tbls}`;") -> toArray();
    $this->onRepair($tables);

    return $out;
  }

  public function optimize($tables) {
    $tbls = $tables;
    if (is_array($tbls))
      $tbls = implode("`, `", $tbls);

    $out = $this->execute("OPTIMIZE TABLE `{$tbls}`;")->toArray();
    $this->onOptimize($tables);

    return $out;
  }

  // STRUCTURAL EXPORT AND QUERY GENERATING

  public function exportView($view, $trim_options = false) {
    $body = null;
    $row = $this->execute("SHOW CREATE VIEW `$view`;")->toRow();
    if (count($row)) {
      $body = $row["Create View"];
      if ($trim_options) {
        $body = preg_replace('/^(CREATE) (.*) (VIEW) (.*)$/',
            '${1} ${3} ${4}', $body);
      }
    } else {
      $body = "-- ".mysql_error($this->link)."\n";
    }

    return $body;
  }

  public function exportTableStructure($table, $temporary = false) {
    if ($temporary)
      $temporary = "TEMPORARY";

    $field_data = $this->execute("DESCRIBE `$table`;")->toArray();
    if (($e = mysql_error($this->link)) != "")
      return "-- {$e} \n";

    foreach ($field_data as $row) {
      $fields[] = "`" . $row["Field"] . "` " . $row["Type"] . " "
          . (($row["Null"]=='NO') ? "NULL" : "NOT NULL");
    }

    $fields = implode(", \n\t", $fields);
    $query = "CREATE $temporary TABLE `$table` (\n\t$fields\n)";

    return $query;
  }

  // Returns null on error.
  function compareTableStructure($a, $b, $only_field_names = false,
      $ignore_field_order = false) {
    $dataform = ($ignore_field_order) ? "arrmap" : "arr";

    $fd_a = $this->execute("DESCRIBE $a;")->$dataform();
    if (($e = mysql_error($this->link)) != "")
      return null;

    $fd_b = $this->execute("DESCRIBE $b;")->$dataform();
    if (($e = mysql_error($this->link)) != "")
      return null;

    if (count($fd_a)!=count($fd_b))
      return false;

    foreach ($fd_a as $i=>$row) {
      $field_a =
          $fd_a[$i]["Field"]
          . ((!$only_field_names) ?
              " " . $fd_a[$i]["Type"] . " " . $fd_a[$i]["Null"] :
              "");

      $field_b =
          $fd_b[$i]["Field"]
          . ((!$only_field_names) ?
              " " . $fd_b[$i]["Type"] . " " . $fd_b[$i]["Null"] :
              "");

      if ($field_a != $field_b)
        return false;

    }

    return true;
  }

  private function prepareStorage() {
    if ($this->storage_prepared)
      return;

    $this->execute("create table if not exists `__stored_values` (
      `variable` varchar (64) not null primary key,
      `value` text not null);");

    if ($this->getError()=='')
      $this->storage_prepared = true;

  }

  public function storeValue($variable, $value) {
    $this->prepareStorage();
    $this->insertupdate('__stored_values',
        array("variable"=>$variable, "value"=>$value));
  }

  public function loadValue($variable) {
    $this->prepareStorage();
    $escaped_value = mysql_real_escape_string($variable, $this->link);
    $query = '
        select
          `value` from `__stored_values`
        where
          `variable` = "' . $escaped_value . '"
        ;';

    $result = $this->execute($query)->toCell();
    return $result;
  }

  public function clearValue($variable) {
    $this->prepareStorage();

    $query = 'delete from `__stored_values` where `variable` = "'
        . mysql_real_escape_string($variable, $this->link) . '";';
    $this->execute($query);

    $result = $this->getError() != '';

    return $result;
  }

  public function existsValue($variable) {
    $this->prepareStorage();
    $query = 'select count(*) c from `__stored_values` where `variable` ="'
        . mysql_real_escape_string($variable, $this->link) . '";';
    $exists = intval($this->execute($query)->toCell()) > 0;

    return $exists;
  }

  private $mysqli;

  private $preparedStatement;
  private $preparedStatementParamTypes;
  private $refArray;

  public function prepare($query, $types) {

    if (!isset($this->mysqli)) {
      $this->mysqli = new mysqli(
          $this->host, $this->username, $this->password, $this->database);
    }

    if (!($this->preparedStatement = $this->mysqli->prepare($query)))
      throw new Exception($this->mysqli->error);

    $this->preparedStatementParamTypes =  $types;

    return $this;
  }

  // execute prepared statement
  public function executeWith() {
    $values = func_get_args();
    $params = array();
    $params[] = $this->preparedStatementParamTypes;

    foreach ($values as $i=>$value)
      $params[] = &$values[$i];

    // bind the params
    call_user_func_array(
        array($this->preparedStatement, 'bind_param'),
        $params);

    $this->preparedStatement->execute();
    $this->preparedStatement->store_result();

    // The bindings.
    $this->refArray = array();
    $this->preparedStatementResultParams = array();

    $md = $this->preparedStatement->result_metadata();

    while($field = $md->fetch_field()) {
      $this->refArray[$field->name] = null;
      $this->preparedStatementResultParams[$field->name] =
        &$this->refArray[$field->name];
    }

    call_user_func_array(
        array($this->preparedStatement, 'bind_result'),
        $this->preparedStatementResultParams);

    // store the statement as the result to get picked up by toArray
    $this->result = $this->preparedStatement;

    return $this;
  }

}
