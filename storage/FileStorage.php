<?
include_once(dirname(__FILE__)."/../base/Base.php");

class FileStorage extends Storage {
	protected $filename;
	
	function __construct($filename) {
		$this->filename = $filename;
		parent::__construct();
	}
	
	function load() {
		if (file_exists($this->filename)) {
			$contents = file_get_contents($this->filename);
			$contents = substr($contents,10);
			$contents = substr($contents,0,strlen($contents)-3);
			eval('$a = ' . $contents);
			$this->setData( $a );
		} else {
			throw new Exception('Non existing storage file ' . $this->filename);
		}
		$this->onLoad($this->getData());
	}
	
	function store() {
		if (!$this->hasDataChanged()) return;
		
		$data = $this->getData();
		
		$output = var_export($data,true);
		if (!file_put_contents($this->filename,'<? return '.$output.'; ?>',LOCK_EX)) {
			throw new Exception('Cannot write storage to file!');
		};
		
 		$this->onStore($data);
	}

}

?>