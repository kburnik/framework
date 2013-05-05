<?
include_once(dirname(__FILE__)."/../base/Base.php");

class SyncStorage extends FileStorage {
	
	private $queriedDataProvider;
	
	function read($variable) {
		$this->load();
		return parent::read($variable,$value);
	}
	
	function write($variable,$value) {
		parent::write($variable,$value);
		$this->store();
	}	

	// override
	function __destruct() {
		// do not store on destruct 
		// do nothing
	}

}

?>