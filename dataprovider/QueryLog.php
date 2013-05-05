<?
include_once(dirname(__FILE__)."/../base/Base.php");
class QueryLog extends Log {

	private $queriedDataProvider;
	private $prepared = false;
	public function __construct($queriedDataProvider) {
		$this->queriedDataProvider = $queriedDataProvider;
	}
	
	private function prepare() {
		if (!$this->prepared) {			
			$this->queriedDataProvider->prepareTable(
				'__query_log',
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
		
		$this->queriedDataProvider->insert('__query_log',$data);
		
		
		$this->writing = false;
	}
	
}

?>