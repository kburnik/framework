<?php

class QDPLog implements ILog {

  private $logTable;
  private $queriedDataProvider;

  private function prepare() {
    if (!$this->prepared) {
      $this->queriedDataProvider->prepareTable(
        $this->logTable,
        array(
           'date'=>'datetime not null'
          , 'timestamp'=>'bigint(8) not null'
          , 'level'=>'varchar(32) not null'
          , 'tag'=>'varchar(128) not null'
          , 'text'=>'text'
          , 'data'=>'text'
        )
      );
      $this->prepared = true;
    }
  }

  public function __construct( $logTable = null , $queriedDataProvider = null ) {


    // check if log file is writeable

    if ($logTable == null) {
      $logTable ="__qdp_log";
    }

    $this->logTable = $logTable;

    if ($queriedDataProvider === null) {
      $queriedDataProvider = Project::GetQDP();
    }

    $this->queriedDataProvider = $queriedDataProvider;

    // prepare the log table
    $this->prepare();
  }

  // overridebale (default implementation for encoding the LOG data)
  public function encode($data) {
    return json_encode($data);
  }

  public function write($tag,$text,$level = 'VERBOSE',$data = array()) {
    $text = $this->encode($text);
    $data = $this->encode($data);
    $date = date("Y-m-d H:i:s");
    $timestamp = intval(microtime(true)*1000);

    $this->queriedDataProvider->insert($this->logTable,array(
        "date" => $date
      , "timestamp" => $timestamp
      , "level" => $level
      , "tag" => $tag
      , "text" => $text
      , "data" => $data
    ));

    return ($this->queriedDataProvider->getAffectedRowCount() > 0);
  }

  public function clear(){
    $this->queriedDataProvider->truncate($this->logTable);
  }


  public function tail( $numLines ) {

    $count = $this->queriedDataProvider->execute("select count(*) c from `{$this->logTable}`;")->toCell();

    $preceedingLineNumber = $count - $numLines;

    if ($preceedingLineNumber > 0) {
      $filter = SQLFilter::Create()->setLimit( $preceedingLineNumber )->setOrder('id asc');

      // delete the rows
      $result = $this->queriedDataProvider->delete( $this->logTable, $filter );

      // optimize to get rid of overhead
      $this->queriedDataProvider->optimize($this->logTable);

    } else {
      $result = null;
    }

    return $result;
  }

  public function getLogTable() {
    return $this->logTable;
  }

  public function readTop( $numBottomLines = 1000 ) {
    $data = $this->queriedDataProvider->execute("
      select
        `date`,`timestamp`,`level`,`tag`,`text`,`data`
      from
        `{$this->logTable}`
      order by
        id desc
      limit {$numBottomLines}
      ;
    ")->toArray();
    return produce("\${\$[\t]{[*]}\n}",$data);
  }

}

