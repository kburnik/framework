<?

class MySQLProviderTestModule extends TestModule
{

  private $db = "eval_framework";
  private $connected = false;
  private $view = null;
  private $table = null;
  private $handler;

  function __construct()
  {
    parent::__construct(new MySQLProvider( "localhost","eval_framework","webhttp80",$this->db ));

    $this->handler = new MySQLProviderTestEventHandler(  );
    $this->base->addEventHandler($this->handler);
  }

  private function connect()
  {
    $this->base->connect();
    $this->connected = true;
  }

  private function disconnect() {
    $this->base->disconnect();
    $this->connected = false;
  }

  private function createTestTable($temporary = true) {
    $this->table = "tbl_".md5(microtime(true));
    $queries = array(
      "CREATE ".(($temporary) ? "TEMPORARY" :"")." TABLE `{$this->table}` (
          `id` INT(4) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
          `val` INT(4) UNSIGNED NOT NULL
      )",
      "INSERT INTO `{$this->table}` (val) values (1),(2),(3),(4),(5),(6),(7),(8),(9),(10)",
    );

    $queries = implode(";\n",$queries);
    $r = $this->base->executeAll( $queries );
    $this->assertEquality($r,1,true);
    $this->assertEquality($this->base->getError(),'',true);
  }

  private function dropTestTable() {
    $this->base->execute("drop table `{$this->table}`;");
    $this->assertEquality($this->base->getError(),'',true);
  }

  private function createTestView() {
    $this->createTestTable();
    $this->view = "view_".md5(microtime(true));
    $q = "create view `{$this->view}` as select database();";
    $this->base->execute($q);
    $this->assertEquality($this->base->getError(),'',true);
  }

  private function dropTestView() {
    $q = "drop view `{$this->view}`;";
    $this->base->execute($q);
    $this->assertEquality($this->base->getError(),'',true);
  }

  public function testConnect() {
    $this->disconnect();
    $this->connect();
    $db = $this->base->execute("select database();")->toCell();
    $this->assertEquality($db,$this->db,true,"Connected to database");
  }

  public function testDisconnect() {

    $this->disconnect();

    $this->connect();
    $this->assertEquality($this->base->getError(),'',true);

    $db = $this->base->execute("select database();")->toCell();
    $this->assertEquality($db,$this->db,true,"Connected to database");


    $this->disconnect();

  }

  public function testGetDatabase() {
    $this->connect();
    $this->assertEquality($this->db, $this->base->getDatabase(),true,"Test get database");
  }

  public function testExecute() {
    $this->connect();
    $this->assertEquality($this->base->getError(),'',true);

    $this->base->prepareTable('temp_table',array('val'=>'varchar(64)  not null'));

    $tables = $this->base->getTables();
    $this->assertEquality($this->base->getError(),'',true);



    $arr = $this->base->execute("show full fields from `{$tables[0]}`")->toArray();
    $this->assertEquality($this->base->getError(),'',true);

    $fields = array("Field","Type","Collation","Null","Key","Default","Extra","Privileges","Comment");
    $this->assertEquality(array_keys($arr[0]),$fields,true);

  }

  public function testExecuteAll() {
    $tables = $this->base->getTables();
    $this->assertEquality($this->base->getError(),'',true);


    $this->createTestTable();


    $row = $this->base->execute("select count(*) c,sum(val) s from `{$this->table}`")->toRow();
    $this->assertEquality($this->base->getError(),'',true);
    $this->assertEquality($row,array ( 'c' => '10', 's' => '55'),true);

    $this->dropTestTable();

  }

  public function testUseDatabase() {
    $this->connect();
    $this->assertEquality($this->base->getError(),'',true);


    $this->assertEquality($this->base->useDatabase($this->db)->getDatabase(),$this->db);

  }

  public function testIsDatabase() {
    $this->assertEquality($this->base->isDatabase($this->db),true);
  }

  public function testToCell() {
    $this->createTestTable();

    $c = $this->base->execute("select count(*) c, sum(val) s from  `{$this->table}`")->toCell();
    $this->assertEquality($this->base->getError(),'',true);

    $s = $this->base->execute("select sum(val) s, count(*) c from  `{$this->table}`")->toCell();
    $this->assertEquality($this->base->getError(),'',true);

    $a = array($c,$s);

    $this->assertEquality($a,array(10,55),true);

    $this->dropTestTable();
  }

  public function testToRow() {
    $this->createTestTable();
    $a = $this->base->execute("select count(*) c, sum(val) s from `{$this->table}`")->toRow();
    $this->assertEquality($a,array("c" => 10,"s" => 55),true);
    $this->dropTestTable();
  }

  public function testToVector() {
    $this->createTestTable();
    $t = array(1,2,3,4,5,6,7,8,9,10);

    $v = $this->base->execute("select val from `{$this->table}`")->toVector();
    $this->assertEquality($this->base->getError(),'',true);
    $this->assertEquality($t,$v,true);

    $v = $this->base->execute("select id from `{$this->table}`")->toVector();
    $this->assertEquality($this->base->getError(),'',true);
    $this->assertEquality($t,$v,true);

    $this->dropTestTable();
  }

  private function multiply($x) { return $x * 10;}

  public function testToArray() {
    $this->createTestTable();

    $t = array(1,2,3,4,5,6,7,8,9,10);
    $mt = array_map(array($this,"multiply"),$t);
    $test_array = array();
    foreach ($t as $i=>$id) {
      $test_array[] = array( "id" => $id , "val" => $id , "valmult10" => $mt[$i] );
    }


    $arr = $this->base->execute("select id,val,val*10 as valmult10 from `{$this->table}`")->toArray();
    $this->assertEquality($this->base->getError(),'',true);
    $this->assertEquality($arr,$test_array,true);

    $this->dropTestTable();
  }

  public function testToArrayMap() {
    $this->createTestTable();

    $t = array(1,2,3,4,5,6,7,8,9,10);
    $test_array = array();
    foreach ($t as $i=>$id) {
      $test_array[$id*100] = array( "valmult100" => $id * 100 , "id" => $id  );
    }

    $arr = $this->base->execute("select val*100 as valmult100, id from `{$this->table}`")->toArrayMap();
    $this->assertEquality($this->base->getError(),'',true);
    $this->assertEquality($arr,$test_array,true);
    $this->dropTestTable();
  }

  public function testToPairMap() {
    $this->createTestTable();
    $t = array(1,2,3,4,5,6,7,8,9,10);
    $test_array = array();
    foreach ($t as $i=>$id) {
      $test_array[$id*100] = $id;
    }

    $arr = $this->base->execute("select val*100 as valmult100, id from `{$this->table}`")->toPairMap();
    $this->assertEquality($this->base->getError(),'',true);

    $this->assertEquality($arr,$test_array,true);
    $this->dropTestTable();
  }

  public function testToArrayGroup() {
    $this->createTestTable();

    $test_array = array (
        100 =>
        array (
        0 =>
        array (
          'val' => '100',
          'id' => '1',
        ),
        1 =>
        array (
          'val' => '100',
          'id' => '2',
        ),
        2 =>
        array (
          'val' => '100',
          'id' => '3',
        ),
        ),
        200 =>
        array (
        0 =>
        array (
          'val' => '200',
          'id' => '4',
        ),
        1 =>
        array (
          'val' => '200',
          'id' => '5',
        ),
        2 =>
        array (
          'val' => '200',
          'id' => '6',
        ),
        ),
        300 =>
        array (
        0 =>
        array (
          'val' => '300',
          'id' => '7',
        ),
        1 =>
        array (
          'val' => '300',
          'id' => '8',
        ),
        2 =>
        array (
          'val' => '300',
          'id' => '9',
        ),
        ),
    );


    $this->base->execute("truncate table `{$this->table}`;");
    $this->assertEquality($this->base->getError(),'',true);

    $this->base->execute("insert into `{$this->table}` (val) values (100),(100),(100),(200),(200),(200),(300),(300),(300);");
    $this->assertEquality($this->base->getError(),'',true);

    $arr = $this->base->execute("select val, id from `{$this->table}`")->toArrayGroup();
    $this->assertEquality($arr,$test_array,true);

    $this->dropTestTable();
  }

  public function testInsert() {
    $this->createTestTable();

    $this->base->execute("truncate table `{$this->table}`;");
    $this->assertEquality($this->base->getError(),'',true);

    $data = array("id"=>100,"val"=>100);
    $id = $this->base->insert($this->table,$data);
    $this->assertEquality($id,100,true);

    $seldata = $this->base->execute("select * from `{$this->table}` where id = '100'")->toRow();
    $this->assertEquality($data,$seldata,true);

    $this->base->execute("truncate table `{$this->table}`;");
    $this->assertEquality($this->base->getError(),'',true);

    $data = array(
      array("id"=>100,"val"=>1000),
      array("id"=>200,"val"=>2000),
      array("id"=>300,"val"=>3000),
    );
    $id = $this->base->insert($this->table,$data);
    $this->assertEquality($id,300,true);
    $this->assertEquality($this->base->getAffectedRowCount(),3,true);

    $seldata = $this->base->execute("select * from `{$this->table}`")->toArray();
    $this->assertEquality($data,$seldata,true);

    $this->dropTestTable();
  }

  public function testUpdate() {
    $this->createTestTable();

    // test 1
    $filter = SQLFilter::create();
    $aff = $this->base->update($this->table,array("val"=>313),$filter);
    $this->assertEquality($aff,10,true);

    $seldata = $this->base->execute("select sum(val) from `{$this->table}`")->toCell();
    $this->assertEquality($seldata,3130,true);


    // test 2
    $filter = SQLFilter::create()->setWhere("id between 1 and 5");
    $aff = $this->base->update($this->table,array("val"=>517),$filter);
    $this->assertEquality($aff,5,true);

    $seldata = $this->base->execute("select sum(val) from `{$this->table}`")->toCell();
    $this->assertEquality($seldata,4150,true);

    // test 3
    $filter = SQLFilter::create()->setWhere("id % 2 = 0")->setLimit(3);

    $aff = $this->base->update($this->table,array("val"=>1729),$filter);
    $this->assertEquality($aff,3,true);

    $seldata = $this->base->execute("select sum(val) from `{$this->table}`")->toCell();
    $this->assertEquality($seldata,7990,true);



    $this->dropTestTable();
  }

  public function testInsertUpdate() {
    $this->createTestTable();

    $data = array("id"=>5,"val"=>10000);
    $r = $this->base->insertUpdate($this->table,$data);

    $seldata = $this->base->execute("select sum(val) from `{$this->table}`")->toCell();
    $this->assertEquality($seldata,10050,true);

    $this->dropTestTable();
  }

  public function testDelete() {
    $this->createTestTable();

    // test 2
    $filter = SQLFilter::create()->setWhere("id between 1 and 5");
    $aff = $this->base->delete($this->table,$filter);
    $this->assertEquality($aff,5,true);

    $seldata = $this->base->execute("select sum(val) from `{$this->table}`")->toCell();
    $this->assertEquality($seldata,40,true);

    $this->dropTestTable();
  }

  public function testGetResult() {
    $this->base->execute("select database();");
    $this->assertEquality($this->base->getResult(),null,false);
  }

  public function testGetRowCount() {
    $this->createTestTable();
    $seldata = $this->base->execute("select * from `{$this->table}`")->toArray();
    $count = $this->base->getRowCount();
    $this->assertEquality($count,10,true);
    $this->dropTestTable();
  }

  public function testGetAffectedRowCount() {
    $this->createTestTable();

    $filter = SQLFilter::create();
    $this->base->update($this->table,array("val"=>313),$filter);
    $aff = $this->base->getAffectedRowCount();
    $this->assertEquality($aff,10,true);

    $this->dropTestTable();
  }

  public function testGetError() {
    // test 1
    $this->createTestTable();
    $this->base->execute("select * from `{$this->table}`; ");
    $err = $this->base->getError();
    $exp = "";
    $this->assertEquality($err,$exp,true);
    $this->dropTestTable();


    // test 2
    $this->createTestTable();
    $this->dropTestTable();

    $this->base->execute("select * from `{$this->table}`; ");
    $err = $this->base->getError();
    $exp = "Table '{$this->db}.{$this->table}' doesn't exist";
    $this->assertEquality($err,$exp,true);
  }

  public function testGetErrorNumber() {
    // test 1
    $this->createTestTable();
    $this->base->execute("select * from `{$this->table}`; ");
    $err = $this->base->getErrorNumber();
    $exp = "";
    $this->assertEquality($err,$exp,true);
    $this->dropTestTable();


    // test 2
    $this->createTestTable();
    $this->dropTestTable();

    $this->base->execute("select * from `{$this->table}`; ");
    $err = $this->base->getErrorNumber();
    $exp = 1146;
    $this->assertEquality($err,$exp,true);
  }

  public function testGetObjects() {
    $objects = $this->base->getObjects();
    $this->assertEquality(is_array($objects) && count($objects) > 0,true,true);
  }

  public function testGetTables() {
    $objects = $this->base->getTables();
    $this->assertEquality(is_array($objects) && count($objects) > 0,true,true);
  }

  public function testGetViews() {

    $this->createTestView();

    $objects = $this->base->getViews();
    if (count($objects)) {
      $res = array_keys( $this->base->execute("show create view `{$objects[0]}`")->toRow() );
      $this->assertEquality($res[0],'View',true,true);
    } else {
      $this->assertEquality(is_array($objects),true,true);
    }
  }

  public function testGetTableDetails()
  {

    $details = $this->base->getTableDetails( );

    foreach ( $details as $tbl_details ) {
      $this->assertEquality( array (
          0 => 'Name',
          1 => 'Engine',
          2 => 'Version',
          3 => 'Row_format',
          4 => 'Rows',
          5 => 'Avg_row_length',
          6 => 'Data_length',
          7 => 'Max_data_length',
          8 => 'Index_length',
          9 => 'Data_free',
          10 => 'Auto_increment',
          11 => 'Create_time',
          12 => 'Update_time',
          13 => 'Check_time',
          14 => 'Collation',
          15 => 'Checksum',
          16 => 'Create_options',
          17 => 'Comment',
        ) , array_keys( $tbl_details ), true );
    }

  }

  public function testGetFields()
  {

    $fields = $this->base->getFields( $this->table );

    $this->assertEquality( array( 'id' , 'val' ) , $fields );


  }

  public function testGetFieldDetails() {

    $fields = $this->base->getFieldDetails( $this->table );

    $this->assertEquality( array (
      0 =>
      array (
        'Field' => 'id',
        'Type' => 'int(4) unsigned',
        'Collation' => NULL,
        'Null' => 'NO',
        'Key' => 'PRI',
        'Default' => NULL,
        'Extra' => 'auto_increment',
        'Privileges' => 'select,insert,update,references',
        'Comment' => '',
      ),
      1 =>
      array (
        'Field' => 'val',
        'Type' => 'int(4) unsigned',
        'Collation' => NULL,
        'Null' => 'NO',
        'Key' => '',
        'Default' => NULL,
        'Extra' => '',
        'Privileges' => 'select,insert,update,references',
        'Comment' => '',
      ),
      ) , $fields );

  }

  public function testGetEnum()
  {

    $this->base->prepareTable('enum_table',array( 'enum_val' =>"enum('red','green','blue')" ));

    $enum = $this->base->getEnum( 'enum_table' , 'enum_val' );

    $this->assertEquality( array( 'red' , 'green' , 'blue' ) , $enum );

    $this->base->drop('enum_table');

  }

  public function testGetPrimaryKey()
  {
    $primarykey = $this->base->getPrimaryKey( $this->table );

    $this->assertEquality( 'id' , $primarykey );
  }

  public function testDrop() {
    $this->createTestTable(false);
    $before = $this->base->getTables();
    $this->base->drop($this->table);

    $after = $this->base->getTables();

    $diff = reset(array_diff($before,$after));

    $this->assertEquality($diff,$this->table,true);

    // NOTE: do not drop since already dropped
  }

  public function testTruncate() {
    $this->createTestTable();
    $cb = $this->base->execute("select count(*) c from `{$this->table}`")->toCell();
    $this->assertEquality($cb,10,true);

    $this->base->truncate($this->table);
    $ca = $this->base->execute("select count(*) c from `{$this->table}`")->toCell();
    $this->assertEquality($ca,0,true);

    $this->dropTestTable();

  }

  public function testRepair() {
    $this->createTestTable();

    $r = $this->base->repair($this->table);
    $r = implode(',',array_keys($r[0]));
    $exp = "Table,Op,Msg_type,Msg_text";

    $this->assertEquality($r,$exp,true);

    $this->dropTestTable();
  }

  public function testOptimize() {
    $this->createTestTable();

    $r = $this->base->optimize($this->table);
    $r = implode(',',array_keys($r[0]));
    $exp = "Table,Op,Msg_type,Msg_text";
    $this->assertEquality($r,$exp,true);

    $this->dropTestTable();
  }

  public function testExportView() {
    $this->createTestView();
    $res = $this->base->exportView($this->view);

    $row = $this->base->execute("SHOW CREATE VIEW `{$this->view}`;")->toRow();
    if (count($row)) {
      $exp = $row["Create View"];
      $this->assertEquality($res,$exp,true);
    }

    $res = $this->base->exportView($this->view,true);
    if (count($row)) {
      $exp = $row["Create View"];
      $exp = preg_replace('/^(CREATE) (.*) (VIEW) (.*)$/','${1} ${3} ${4}',$exp);
      $this->assertEquality($res,$exp,true);
    }


    $this->dropTestView();
  }

  public function testGetBaseClasses() {
    $this->assertEquality(true,true,true);
  }

  public function simpleEvent($query) {
    $this->simple_event_query = $query;
  }

  public function testAddEventListener() {

    $this->base->addEventListener('onExecuteStart',array($this,'simpleEvent'));

    // test 1
    $query = "select database();";
    $this->base->onExecuteStart($query);
    $this->assertEquality($query,$this->simple_event_query,true);

    // test 2
    $query = "select database(),database();";
    $this->base->execute($query);
    $this->assertEquality($query,$this->simple_event_query,true);

  }


}

class MySQLProviderTestEventHandler implements IQueriedDataProviderEventHandler {
  var $tested_events = array();
  public function onConnect($host,$username,$password,$database) {
    $this->tested_events[__FUNCTION__] = true;
  }

  public function onDisconnect() {
    $this->tested_events[__FUNCTION__] = true;
  }


  public function onExecute( $query, $result ) {
    $this->tested_events[__FUNCTION__] = true;
  }

  public function onExecuteStart( $query ) {
    $this->tested_events[__FUNCTION__] = true;
  }

  public function onExecuteComplete( $query , $result ) {
    $this->tested_events[__FUNCTION__] = true;
  }

  public function onInsert( $table, $data ) {
    $this->tested_events[__FUNCTION__] = true;
  }

  public function onUpdate( $table, $data, $filter ) {
    $this->tested_events[__FUNCTION__] = true;
  }

  public function onInsertUpdate( $table, $data ) {
    $this->tested_events[__FUNCTION__] = true;
  }

  public function onDelete( $table, $filter ) {
    $this->tested_events[__FUNCTION__] = true;
  }

  public function onDrop($tables) {
    $this->tested_events[__FUNCTION__] = true;
  }

  public function onTruncate($tables) {
    $this->tested_events[__FUNCTION__] = true;
  }

  public function onRepair($tables) {
    $this->tested_events[__FUNCTION__] = true;
  }

  public function onOptimize($tables) {
    $this->tested_events[__FUNCTION__] = true;
  }

  public function onError( $query, $error, $errno ) {
    // echo $query;
    $this->tested_events[__FUNCTION__] = true;
  }

}


?>