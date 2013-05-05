<?
include_once(dirname(__FILE__)."/../base/Base.php");

class QueryLog implements ILog {

	private $logTable;
	private $queriedDataProvider;
	private $prepared = false;
	
	
	public function __construct($queriedDataProvider) {
		$this->logTable = '__query_log';
		$this->queriedDataProvider = $queriedDataProvider;
	}
	
	private function prepare() {
		if (!$this->prepared) {			
			$this->queriedDataProvider->prepareTable(
				$this->logTable,
				array(
					  'tag'=>'varchar(16) not null'
					, 'text'=>'varchar(64) not null'
					, 'level'=>'varchar(64) not null'
					, 'date'=>'datetime not null'
					, 'runtime'=>'int(4) unsigned not null'
					, 'threads'=>'int(2) unsigned not null'
					, 'url'=>'varchar(256) not null'
					, 'backtrace'=>'text not null'
				)
			);			
			$this->prepared = true;
		}
	}
	
	public function write($tag,$text,$level = 'VERBOSE',$data = array()) { 
		
		$this->prepare();
				
		if ($this->writing) return;
		$this->writing = true;
		
		$data['tag'] = $tag;
		$data['text'] = $text;
		$data['level'] = $level;
		$data['date'] = now();
		$data['url'] = Page::GetURL();
		
		//echo "Writing log";
		
		$this->queriedDataProvider->insert($this->logTable,$data);
		
		
		$this->writing = false;
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
					`tag`
					, `text`
					, `level`
					, `date`
					, `runtime`
					, `threads`
					, `url`
					, `backtrace`
			from 
				`{$this->logTable}` 
			order by 
				id desc 
			limit {$numBottomLines} 
			;
		")->toArray();
		return produce("\${\$[\t]{[*]}\n}",$data);
	}

	
	public function encode($data){
		return json_encode($data);
	}
	
	
	
}

?>