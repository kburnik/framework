<?
include_once(dirname(__FILE__)."/Base.php");

/*
Cache abstract class, used for cached data,
provide method to get data and to check if cache needs refreshing
also provide method to store and load data (i.e. from/to a file)
*/

abstract class Cache extends Storage {

	// get fresh data method
	abstract protected function getFreshData($variable);
	
	// see if a new getFreshData is needed
	abstract protected function isFresh($variable);

	////////////////////////////////////////////
	
	private $storage;
	
	function __construct($storage){
		$this->storage = $storage;
	}

	// get the last change timestamp
	function getLastChange($variable = 'default') {		
		return $this->getStorage()->read('changed-'.$this->varHash($variable));
	}
	
	// get number of seconds elapsed since last change (fresh data write) occured
	function getLastChangeElapsed($variable = 'default') {
		
		return microtime(true) - $this->getLastChange($variable);
	}
	
	
	// get the storage object used to save and load data
	public function getStorage() {
		return $this->storage;
	}
	
	private function varHash($variable) {
		return md5(var_export($variable,true));
	}
	
	public function read($variable = 'default') {
		$varhash = $this->varHash($variable);
		if ($this->isFresh($variable)) {
			$result = $this->getStorage()->read('cache-'.$varhash);
		} else {
			$result = $this->getFreshData($variable);
			$this->getStorage()->write('cache-'.$varhash,$result);
			$this->getStorage()->write('changed-'.$varhash,microtime(true));
		}
		
		return $result;
	}
	
	public function clear($variable = 'default') {		
		$varhash = $this->varHash($variable);
		$this->getStorage()->clear('cache-'.$varhash);
		$this->getStorage()->clear('changed-'.$varhash);
	}
	
	public function exists($variable = 'default') {
		return $this->getStorage()->exists('cache-'.$this->varHash($variable));
	}
	
	function load() {
	}
	
	function store() {
	}

}

?>