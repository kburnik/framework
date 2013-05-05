<?
include_once(dirname(__FILE__)."/../base/Base.php");

class MySQLProviderEventHandler implements IQueriedDataProviderEventHandler {

	public function onConnect($host,$username,$password,$database) {		
		Console::WriteLine("Connect to database $database @ $host with username $username and password  $password ");
	}
	
	public function onDisconnect() {
		Console::WriteLine("Disconnecting");	
	}
	
	public function onExecute($query,$result) {
		Console::WriteLine("Executing query $query");
	}
	
	public function onInsert( $table, $data ) {}
	
	public function onUpdate( $table, $data, $filter ) {}
	
	
	public function onInsertUpdate( $table, $data ) {}
	
	public function onDelete( $table, $filter ) {}
	
	
	
	public function onDrop($tables) {}
	
	public function onTruncate($tables) {}
	
	public function onRepair($tables) {}
	
	public function onOptimize($tables) {}
	
	
	public function onError( $query, $error, $errno ) {
		print_r( func_get_args() );
	}
	
	
}

?>