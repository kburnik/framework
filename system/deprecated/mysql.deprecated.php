<?
// THE MYSQL CLASS
class MySQLProvider implements IQueriedDataProvider {
	var $link = null;
	var $cache = array();
	var $query = "";
	var $result = null;
	var $worktimes = array();
	var $last_query = "";
	var $last_query_worktime = 0;
	var $parent_object = null;
	var $count = 0; // count of queries executed
	var $current_database;
	
	// construction connection strings:
	var $host,$username,$password,$database;
	
	// BASIC
	function __construct($host,$username=null,$password=null,$database=null) {
		$this->host = $host; $this->username = $username; $this->password=$password; $this->database = $database;
		$this->connect($host,$username=null,$password=null,$database=null);
		file_put_contents(dirname(__FILE__)."/last_session_queries.sql","");
		return $this;
	}
	
	function __destruct() {
		$this->close();
	}
	
	function connect($host,$username=null,$password=null,$database=null) {
		if (is_array($host)) extract($host);
		$this->link = mysql_connect($host,$username,$password);
		$this->current_database = $database;
		mysql_select_db($database);
		return $this->result;
	}
	
	function close() {
		@mysql_close($this->link);
		$this->count++;
	}
	
	function getDatabase() {
		return $this->execute("select database();")->cell();
	}

	function execute($q,$return_result=false) {		
				
		$this->result = $result = mysql_query($q);		
		$errno = $this->getErrorNumber();
		$error = $this->getError();
		
		if ($error != '') {
			$this->onError();
		}	
		
		return $this;
	}

	function executeAll($queries,$delimiter=";",$break_on_error = false) {
		$queries = explode($delimiter,$queries);
		foreach ($queries as $query) {
			if (trim($query)!='') {
				$this->execute($query.";");
				if ($break_on_error && $this->getError()!='') {
					return false;
				}
			}
		}
		
		return true;
	}
	
	public function useDatabase($db) {
		if(!mysql_select_db($db)) {
			$e = mysql_error();
			trigger_error("Database $db :".mysql_errno().": ".$e,E_USER_ERROR);
		}
		$this->current_database = $db;
		$this->count++;
		return $this;
	}
	
	public function isDatabase($db) {
		return mysql_num_rows($this->execute("show databases like '{$db}'")->result) > 0;
	}
	
	// DATA TRANSFORMING
	public function toCell() {
		if ($this->result && mysql_num_rows($this->result)>0) {
			$ff = mysql_fetch_field($this->result,0);
			$result = mysql_result($this->result,0,$ff->name);
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
		$s = micronow();
		$resource = $this->result;
		
		if (!$resource) return null; 
		$out = $row = array();		
		
		while ($row=@mysql_fetch_array($resource, $assoc)) {				
			$out[]=$row;
		}
	
		@mysql_free_result($resource);
		$this->worktimes[] = microdiff($s);
		return $out;
	}
	
	public function toArrayMap() {
		$data = $this->toArray();
		if (count($data)==0) return array();
		foreach($data as $item) {
			$out[reset($item)] = $item;
		}
		return $out;
	}
	
	public function toPairMap($transformations=array()) {
		$data = $this->toArray($transformations);
		if (count($data)==0) return array();
		foreach($data as $item) {
			$out[reset($item)] = array_pop($item);
		}
		return $out;
	}
	
	public function toArrayGroup($field=null,$remove_grouped_field = false,$transformations=array()) {
		$data = $this->toArray($transformations);
		if (count($data)==0) return array();
		// take first field if none is defined
		if ($field === null) $field = reset(array_keys(reset($data)));
		foreach($data as $item) {
			$field_value = $item[$field];
			if ($remove_grouped_field) unset($item[$field]);
			$out[ $field_value ][] = $item;
		}
		return $out;
	}
	
	// QUERY BUILDING
	private function generate_fields($data) {
		$fields=$values=array();
		foreach ($data as $key=>$value) {
				$fields[]="`".$key."`";
				$values[]="\"".mysql_real_escape_string($value)."\""; 
		}
		return array($fields,$values);
	}

	private function filter_fields($mixed=array(),$data) { // filter fields by given list or by table (if string provided)
		$fields = (is_array($mixed))  ? $mixed  : $this->fields($mixed);
		if (is_assoc($data)) {
			$data = array_pick($data,$fields);
		} else {
			foreach ($data as $index=>$row) {
				$data[$index] = array_pick($row,$fields);
			}
		}
		return $data;
	}
		
	private function generate_values($row) {
		return "(\"".implode("\",\"",array_map("mysql_real_escape_string",$row))."\")";
	}

	
	// COMMON QUERIES
	public function insert($table,$data,$auto_filter_fields = true,$on_duplicate_key_update = false) { // inserts a single row or multiple rows into table
		
		// filter data to avoid unwanted/redundant fields;
		if ($auto_filter_fields) $data = $this->filter_fields($table,$data);
		
		if (is_assoc($data)) {
			list($fields,$values) = $this->generate_fields($data);
			$fields="(".implode(",",$fields).")";
			$values="(".implode(",",$values).")";
		} else {
			list($fields,$values) = $this->generate_fields(reset($data));
			$fields="(".implode(",",$fields).")";
			$values = implode(",",array_map(array($this,"generate_values"),$data));
		}
		
		if ($on_duplicate_key_update !== false) {
			$on_duplicate_key_update = "ON DUPLICATE KEY UPDATE {$on_duplicate_key_update}";
		}
		
		$q = "insert into `$table` {$fields} values {$values} {$on_duplicate_key_update};";
		$this->execute($q);
		$out = mysql_insert_id();
		return $out;
	}
	
	public function update($table,$data,$where,$limit='',$exceptions=array(),$auto_filter_fields=true) {
		if ($where) $where="where $where";
		if ($limit) $limit="limit $limit";
		if ($auto_filter_fields) $data = $this->filter_fields($table,$data);
		
		list($fields,$values)=$this->generate_fields($data,$exceptions);
		foreach ($fields as $key=>$field) {
			$updates[]="{$field}={$values[$key]}";
		}
		$updates=implode(",",$updates);
		$q="update `$table` set {$updates} $where $limit;";
		$this->execute($q);
		return $this->affected();
	}
	
	public function insertUpdate($table,$data,$auto_filter_fields=true) {
		// the insert part of the query
		if ($auto_filter_fields) $data = $this->filter_fields($table,$data);
		list($fields,$values)=$this->generate_fields($data);
		$insert_fields="(".implode(",",$fields).")";
		$insert_values="(".implode(",",$values).")";
		
		// the update part of the query
		$updates = array();
		foreach ($fields as $key=>$field) {
			$updates[]="{$field}={$values[$key]}";
		}
		$updates=implode(",",$updates);
		
		$q="insert into `$table` {$insert_fields} values {$insert_values} ON DUPLICATE KEY UPDATE {$updates};";
		$this->execute($q);
		return $this->affected();
	}
	
	public function delete($table,$where='',$limit='') {
		if ($where) $where="where $where";
		if ($limit) $limit="limit $limit";
		$this->execute("delete from `$table` $where $limit;");
		return $this->affected();
	}

	
	// QUERY RESULT
	
	public function getResult() {
		return $this->result;
	}
	
	public function getRowNumber() {
		return mysql_num_rows($this->result);
	}
	
	public function getAffectedRowCount() {
		return mysql_affected_rows();
	}
	
	public function getError() {
		return mysql_error();
	}
	
	public function getErrorNumber() {
		return mysql_errno();
	}
	
	
	// DB STRUCTURE AND DETAILS
	public function getObjects() {		
		// cache the tables for repeated use
		if (!isset( $this->cache[$this->current_database]["tables"]) ) {
			$data = $this->execute("show tables;")->toArray();
			$this->cache[$this->current_database]["tables"] = (count($data)>0) ? reset(rotate_table($data)) : array();
		} 
		
		return $this->cache[$this->current_database]["tables"];
	}
	
	public function getTables() {
		$q = "SHOW FULL TABLES where table_type like 'BASE TABLE'";
		if (!isset( $this->cache[$this->current_database]["justtables"]) ) {
			$data = $this->execute( $q )->toArray();
			$this->cache[$this->current_database]["justtables"] = (count($data)>0) ? reset(rotate_table($data)) : array();
		}
		return $this->cache[$this->current_database]["justtables"];
	}
	
	public function getViews() {
		$q = "SHOW FULL TABLES where table_type like 'VIEW'";
		if (!isset( $this->cache[$this->current_database]["justviews"]) ) {
			$data = $this->execute( $q )->toArray();
			$this->cache[$this->current_database]["justviews"] = (count($data)>0) ? reset(rotate_table($data)) : array();
		}
		return $this->cache[$this->current_database]["justviews"];
	}
	
	public function getTableDetails() {
		if ( !isset( $this->cache[$this->current_database]["table_details"] ) ) {
			$result = $this->execute("SHOW TABLE STATUS;",true);
			while( $row = mysql_fetch_array( $result ) ) {  
				$tables[$row['Name']]=$row;
			}
			$this->cache[$this->current_database]["table_details"] = $tables;
		}
		
		return $this->cache[$this->current_database]["table_details"];
	}

	public function getFields($table) {
		// AVOID QUERYING FOR TABLE FIELDS MORE THAN ONCE PER SCRIPT :: IN OTHER WORDS, CACHE THEM FOR LATER USE
		if (!isset( $this->cache[$this->current_database]["table_fields"][$table] )) {
			$fields = rotate_table($this->field_details($table));
			$this->cache[$this->current_database]["table_fields"][$table] = $fields['Field'];
		}
		return $this->cache[$this->current_database]["table_fields"][$table];
	}
	
	public function getFieldDetails($table) {
		if (!isset($this->cache[$this->current_database]["field_details"][$table])) {
			$this->cache[$this->current_database]["field_details"][$table] = $this -> execute("SHOW FULL FIELDS FROM `$table`;") -> toArray();
		}
		return $this->cache[$this->current_database]["field_details"][$table];
	}
	
	public function getEnum( $table , $field ) {
		if (!isset($this->cache[$this->current_database]["enums"][$table][$field])) {
			$output = array();
			if (in_array($table,$this->tables())) {
				$query = "SHOW COLUMNS FROM `$table` LIKE '$field' ";
				$result = $this->execute( $query , true );
				$row = mysql_fetch_array( $result , MYSQL_NUM );
				$regex = "/'(.*?)'/";
				if (preg_match_all( $regex , $row[1], $enum_array ) > 0) {
					$output = $enum_array[1];
				} 	
			}
			$this->cache[$this->current_database]["enums"][$table][$field] = $output;
		}
		return  $this->cache[$this->current_database]["enums"][$table][$field] ;
	} 
	
	public function getPrimaryKey($table) {
		if (!isset($this->cache[$this->current_database]["primary_key"][$table])) {
			$field_details = $this->field_details($table);
			foreach ($field_details as $row) {
				if ($row['Key']=='PRI') {
					$this->cache[$this->current_database]["primary_key"][$table]  = $row['Field'];
					break;
				}
			}
		}
		
		return $this->cache[$this->current_database]["primary_key"][$table] ;
	}
	
	public function getDatabaseSize() {
		if (!isset($this->cache[$this->current_database]["db_size"])) {
			$t = $this->table_details();
			$size = 0;
			foreach ($t as $table) {
				$size += $table['Data_length']+$table['Index_length'];
			}
			$this->cache[$this->current_database]["db_size"] = $size;
		}
		return $this->cache[$this->current_database]["db_size"];
	}

	
	// TABLE MAINTENANCE
	
	public function drop($tables){
		if (is_array($tables)) $tables = "`".implode("`,`",$tables)."`";
		$this->execute("DROP TABLE IF EXISTS {$tables};");
		return $this->result;
	}
	
	public function truncate($tables){
		if (!is_array($tables))  {
			$tables = explode(",",$tables);
		} 
		foreach ($tables as $table) {
			$this->execute("TRUNCATE TABLE `{$table}`;");
			$r[] = $this->result;
		}
		return $r;
	}
	
	public function repair($tables) {
		if (is_array($tables)) $tables = implode("`,`",$tables);
		return $this->execute("REPAIR TABLE `{$tables}`;") -> toArray();
	}
	
	public function optimize($tables) {
		if (is_array($tables)) $tables = implode("`,`",$tables);
		return $this->execute("OPTIMIZE TABLE `{$tables}`;") -> toArray();	
	}
	
	
	// STRUCTURAL EXPORT AND QUERY GENERATING
	
	public function exportView($view,$trim_options = false) {
		$body = null;
		$row = $this->execute("SHOW CREATE VIEW `$view`;")->toRow();
		if (count($row)) {
			$body = $row["Create View"];
			if ($trim_options) {
				$body = preg_replace('/^(CREATE) (.*) (VIEW) (.*)$/','${1} ${3} ${4}',$body);
			}
		} else {
			$body = "-- ".mysql_error()."\n";
		}
		return $body;
	}
	
	public function exportTableStructure($table,$temporary = false) {
		if ($temporary) $temporary = "TEMPORARY";
		$field_data = $this->execute("DESCRIBE `$table`;")->toArray();
		if (($e = mysql_error()) != "") return "-- {$e} \n";
		
		foreach ($field_data as $row) {
			$fields[] = "`".$row["Field"]."` ".$row["Type"]." ".(($row["Null"]=='NO') ? "NULL" : "NOT NULL");
		}
		
		$fields = implode(",\n\t",$fields);
		
		$query = "CREATE $temporary TABLE `$table` (\n\t$fields\n)";
		
		return $query;
	}
	
	// COMPARE TWO TABLE STRUCTURES // return null on error
	function compareTableStructure($a,$b,$only_field_names = false,$ignore_field_order = false) {
		$dataform = ($ignore_field_order) ? "arrmap" : "arr";
		
		$fd_a = $this->execute("DESCRIBE $a;")->$dataform();
		if (($e = mysql_error()) != "") return null;
		
		$fd_b = $this->execute("DESCRIBE $b;")->$dataform();
		if (($e = mysql_error()) != "") return null;
		
		if (count($fd_a)!=count($fd_b)) return false;
		
		foreach ($fd_a as $i=>$row) {
			$field_a = $fd_a[$i]["Field"] . ((!$only_field_names) ? " ".$fd_a[$i]["Type"]." ".$fd_a[$i]["Null"] : "");
			$field_b = $fd_b[$i]["Field"] . ((!$only_field_names) ? " ".$fd_b[$i]["Type"]." ".$fd_b[$i]["Null"] : "");
			if ($field_a != $field_b) {
				return false;
			}
		}
		
		return true;
		
	}
}

function sql($query=null,$return_result = false) {
	global $_mysql;
	if (!isset($_mysql)) {
		$_mysql = new _mysql( include(__PROJECT_DIR__."/db_connection.php") );
	}
	if ($query!==null) {
		return $_mysql->execute($query,$return_result);
	}
	return $_mysql;
}

function syssql($query=null,$return_result = false) {
	global $_sysmysql;
	if (!isset($_sysmysql)) {
		$_sysmysql = new _mysql( include(__SYSTEM_DIR__."/db_connection.php")  );
	}
	if ($query!==null) {
		return $_sysmysql->execute($query,$return_result);
	}
	return $_sysmysql;

}

?>